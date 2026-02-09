<?php
/**
 * ============================================
 * REALIZAR COMPRA
 * ============================================
 * 
 * Este archivo procesa una compra:
 * 1. Verifica que hay stock suficiente
 * 2. Registra la compra en la base de datos
 * 3. Reduce el stock del producto
 * 
 * MÉTODO: POST
 * 
 * CAMPOS OBLIGATORIOS:
 * - usuario_id (ID del usuario que compra)
 * - producto_id (ID del producto a comprar)
 * 
 * CAMPOS OPCIONALES:
 * - cantidad (por defecto es 1)
 * 
 * EJEMPLO DE USO:
 * 
 * fetch('http://localhost/DWEC/Angular/ProyectoMio/backend/realizar_compra.php', {
 *     method: 'POST',
 *     headers: { 'Content-Type': 'application/json' },
 *     body: JSON.stringify({
 *         usuario_id: 1,
 *         producto_id: 3,
 *         cantidad: 2
 *     })
 * })
 * .then(response => response.json())
 * .then(data => {
 *     if (data.exito) {
 *         // Compra exitosa - redirigir a página de éxito
 *         localStorage.setItem('ultimaCompra', JSON.stringify(data.compra));
 *         window.location.href = 'compra-exitosa.html';
 *     } else {
 *         // Error - mostrar mensaje
 *         alert(data.mensaje);
 *     }
 * });
 */

require_once 'config.php';

// ============================================
// VERIFICAR MÉTODO POST
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Este endpoint solo acepta POST'
    ]);
    exit();
}

// ============================================
// LEER DATOS DEL JSON
// ============================================
$datosJSON = file_get_contents('php://input');
$datos = json_decode($datosJSON, true);

// ============================================
// VALIDAR CAMPOS OBLIGATORIOS
// ============================================
if (!isset($datos['usuario_id']) || !isset($datos['producto_id'])) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Campos obligatorios: usuario_id, producto_id'
    ]);
    exit();
}

// ============================================
// PREPARAR DATOS
// ============================================
$usuarioId = intval($datos['usuario_id']);
$productoId = intval($datos['producto_id']);
$cantidad = isset($datos['cantidad']) ? intval($datos['cantidad']) : 1;

// Validar cantidad
if ($cantidad <= 0) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'La cantidad debe ser mayor que 0'
    ]);
    exit();
}

// ============================================
// PASO 1: VERIFICAR QUE EL PRODUCTO EXISTE Y TIENE STOCK
// ============================================
$sqlProducto = "SELECT id, nombre, precio, stock, imagen FROM productos WHERE id = $productoId";
$resultadoProducto = $conexion->query($sqlProducto);

if ($resultadoProducto->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'El producto no existe'
    ]);
    exit();
}

$producto = $resultadoProducto->fetch_assoc();

// ============================================
// PASO 2: VERIFICAR STOCK SUFICIENTE
// ============================================
$stockActual = intval($producto['stock']);

if ($stockActual === 0) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Este producto está agotado',
        'stock_disponible' => 0
    ]);
    exit();
}

if ($stockActual < $cantidad) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'No hay suficiente stock',
        'stock_disponible' => $stockActual,
        'cantidad_pedida' => $cantidad
    ]);
    exit();
}

// ============================================
// PASO 3: VERIFICAR QUE EL USUARIO EXISTE
// ============================================
$sqlUsuario = "SELECT id, nombre FROM usuarios WHERE id = $usuarioId";
$resultadoUsuario = $conexion->query($sqlUsuario);

if ($resultadoUsuario->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'El usuario no existe'
    ]);
    exit();
}

// ============================================
// PASO 4: REGISTRAR LA COMPRA
// ============================================
$precioUnitario = floatval($producto['precio']);

$sqlCompra = "INSERT INTO compras (usuario_id, producto_id, cantidad, precio_unitario) 
              VALUES ($usuarioId, $productoId, $cantidad, $precioUnitario)";

if (!$conexion->query($sqlCompra)) {
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error al registrar la compra',
        'error' => $conexion->error
    ]);
    exit();
}

$compraId = $conexion->insert_id;

// ============================================
// PASO 5: REDUCIR EL STOCK DEL PRODUCTO
// ============================================
$nuevoStock = $stockActual - $cantidad;

$sqlActualizarStock = "UPDATE productos SET stock = $nuevoStock WHERE id = $productoId";

if (!$conexion->query($sqlActualizarStock)) {
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error al actualizar el stock',
        'error' => $conexion->error
    ]);
    exit();
}

// ============================================
// COMPRA EXITOSA - DEVOLVER DATOS
// ============================================
$totalCompra = $cantidad * $precioUnitario;

echo json_encode([
    'exito' => true,
    'mensaje' => '¡Compra realizada con éxito!',
    'compra' => [
        'id' => $compraId,
        'producto_nombre' => $producto['nombre'],
        'producto_imagen' => $producto['imagen'],
        'cantidad' => $cantidad,
        'precio_unitario' => $precioUnitario,
        'total' => $totalCompra,
        'stock_restante' => $nuevoStock
    ]
]);

$conexion->close();
?>
