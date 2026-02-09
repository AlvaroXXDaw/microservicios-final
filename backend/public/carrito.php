<?php
/**
 * API de Carrito de Compras
 * GET: Obtener carrito del usuario
 * POST: Añadir/actualizar producto
 * DELETE: Eliminar producto del carrito
 */

require_once 'config.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['exito' => false, 'mensaje' => 'No has iniciado sesión']);
    exit();
}

$usuario_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Obtener carrito del usuario con info del producto
        $sql = "SELECT c.id, c.producto_id, c.cantidad, 
                       p.nombre, p.precio, p.imagen, p.stock
                FROM carrito c
                JOIN productos p ON c.producto_id = p.id
                WHERE c.usuario_id = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $carrito = [];
        while ($row = $result->fetch_assoc()) {
            $carrito[] = [
                'id' => (int)$row['producto_id'],
                'nombre' => $row['nombre'],
                'precio' => (float)$row['precio'],
                'imagen' => $row['imagen'],
                'cantidad' => (int)$row['cantidad'],
                'stock' => (int)$row['stock']
            ];
        }
        
        echo json_encode(['exito' => true, 'carrito' => $carrito]);
        break;
        
    case 'POST':
        // Añadir/actualizar producto en carrito
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['producto_id']) || !isset($data['cantidad'])) {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Datos incompletos']);
            exit();
        }
        
        $producto_id = (int)$data['producto_id'];
        $cantidad = (int)$data['cantidad'];
        
        // Verificar stock disponible
        $sqlStock = "SELECT stock FROM productos WHERE id = ?";
        $stmtStock = $conexion->prepare($sqlStock);
        $stmtStock->bind_param("i", $producto_id);
        $stmtStock->execute();
        $resultStock = $stmtStock->get_result();
        
        if ($resultStock->num_rows === 0) {
            echo json_encode(['exito' => false, 'mensaje' => 'Producto no encontrado']);
            exit();
        }
        
        $stock = $resultStock->fetch_assoc()['stock'];
        
        if ($cantidad > $stock) {
            echo json_encode(['exito' => false, 'mensaje' => 'Stock insuficiente', 'stock' => $stock]);
            exit();
        }
        
        // INSERT o UPDATE con ON DUPLICATE KEY
        $sql = "INSERT INTO carrito (usuario_id, producto_id, cantidad) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE cantidad = ?";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("iiii", $usuario_id, $producto_id, $cantidad, $cantidad);
        
        if ($stmt->execute()) {
            echo json_encode(['exito' => true, 'mensaje' => 'Carrito actualizado']);
        } else {
            echo json_encode(['exito' => false, 'mensaje' => 'Error al actualizar carrito']);
        }
        break;
        
    case 'DELETE':
        // Eliminar producto del carrito
        $producto_id = isset($_GET['producto_id']) ? (int)$_GET['producto_id'] : 0;
        
        if ($producto_id === 0) {
            // Vaciar todo el carrito
            $sql = "DELETE FROM carrito WHERE usuario_id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("i", $usuario_id);
        } else {
            // Eliminar producto específico
            $sql = "DELETE FROM carrito WHERE usuario_id = ? AND producto_id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("ii", $usuario_id, $producto_id);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['exito' => true, 'mensaje' => 'Eliminado del carrito']);
        } else {
            echo json_encode(['exito' => false, 'mensaje' => 'Error al eliminar']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
}

$conexion->close();
?>
