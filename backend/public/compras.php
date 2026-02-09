<?php
/**
 * ============================================
 * COMPRAS - Historial de pedidos
 * ============================================
 *
 * GET  -> Devuelve las compras del usuario logueado
 * POST -> Guarda las compras del usuario logueado
 *
 * Body POST:
 * {
 *   "items": [
 *     { "id": 1, "cantidad": 2 },
 *     { "id": 3, "cantidad": 1 }
 *   ],
 *   "facturaId": "FAC-20260205-123abc",
 *   "facturaUrl": "http://host/..."
 * }
 */

require_once 'config.php';

// ============================================
// Verificar que el usuario esta logueado
// ============================================
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'No has iniciado sesion'
    ]);
    exit();
}

$usuarioId = intval($_SESSION['user_id']);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // ============================================
    // GET - Obtener historial de compras
    // ============================================
    case 'GET':
        $sql = "SELECT 
                    c.id AS compra_id,
                    c.cantidad,
                    c.precio_unitario,
                    c.fecha_compra,
                    c.factura_id,
                    c.factura_url,
                    p.id AS producto_id,
                    p.nombre AS producto_nombre,
                    p.imagen AS producto_imagen,
                    (c.cantidad * c.precio_unitario) AS total
                FROM compras c
                INNER JOIN productos p ON c.producto_id = p.id
                WHERE c.usuario_id = ?
                ORDER BY c.fecha_compra DESC";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $result = $stmt->get_result();

        $compras = [];
        $totalGastado = 0;

        while ($row = $result->fetch_assoc()) {
            $compras[] = $row;
            $totalGastado += floatval($row['total']);
        }

        echo json_encode([
            'exito' => true,
            'compras' => $compras,
            'total_compras' => count($compras),
            'total_gastado' => $totalGastado
        ]);
        break;

    // ============================================
    // POST - Guardar compras
    // ============================================
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['items']) || !is_array($data['items']) || count($data['items']) === 0) {
            http_response_code(400);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Items invalidos'
            ]);
            exit();
        }

        $facturaId = isset($data['facturaId']) ? trim($data['facturaId']) : null;
        $facturaUrl = isset($data['facturaUrl']) ? trim($data['facturaUrl']) : null;

        if ($facturaId) {
            $stmtFactura = $conexion->prepare(
                "SELECT factura_id FROM facturas WHERE factura_id = ? AND usuario_id = ?"
            );
            $stmtFactura->bind_param("si", $facturaId, $usuarioId);
            $stmtFactura->execute();
            $resFactura = $stmtFactura->get_result();

            if ($resFactura->num_rows === 0) {
                http_response_code(400);
                echo json_encode([
                    'exito' => false,
                    'mensaje' => 'Factura no valida'
                ]);
                exit();
            }
        }

        $conexion->begin_transaction();

        try {
            $stmtProducto = $conexion->prepare("SELECT precio FROM productos WHERE id = ?");
            $stmtInsert = $conexion->prepare(
                "INSERT INTO compras (usuario_id, producto_id, cantidad, precio_unitario, factura_id, factura_url)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            foreach ($data['items'] as $item) {
                if (!isset($item['id']) || !isset($item['cantidad'])) {
                    throw new Exception('Formato de item incorrecto');
                }

                $productoId = intval($item['id']);
                $cantidad = intval($item['cantidad']);

                if ($productoId <= 0 || $cantidad <= 0) {
                    throw new Exception('Datos de item invalidos');
                }

                // Obtener precio actual
                $stmtProducto->bind_param("i", $productoId);
                $stmtProducto->execute();
                $resultProducto = $stmtProducto->get_result();

                if ($resultProducto->num_rows === 0) {
                    throw new Exception('Producto no encontrado');
                }

                $producto = $resultProducto->fetch_assoc();
                $precioUnitario = floatval($producto['precio']);

                $stmtInsert->bind_param(
                    "iiidss",
                    $usuarioId,
                    $productoId,
                    $cantidad,
                    $precioUnitario,
                    $facturaId,
                    $facturaUrl
                );

                if (!$stmtInsert->execute()) {
                    throw new Exception('Error al guardar la compra');
                }
            }

            $conexion->commit();

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Compras guardadas'
            ]);
        } catch (Exception $e) {
            $conexion->rollback();
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => $e->getMessage()
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode([
            'exito' => false,
            'mensaje' => 'Metodo no permitido'
        ]);
        break;
}

$conexion->close();
?>
