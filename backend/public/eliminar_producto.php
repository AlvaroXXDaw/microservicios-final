<?php
/**
 * ============================================
 * ELIMINAR PRODUCTO (Solo Admin)
 * ============================================
 * 
 * Este archivo permite eliminar productos de la tienda.
 * 
 * MÉTODO: GET (con parámetro id)
 * Usamos GET en lugar de DELETE por simplicidad.
 * 
 * EJEMPLO DE USO:
 * 
 * // Eliminar el producto con ID 5
 * fetch('http://localhost/DWEC/Angular/ProyectoMio/backend/eliminar_producto.php?id=5')
 *     .then(response => response.json())
 *     .then(data => console.log(data));
 * 
 * RESPUESTA EXITOSA:
 * {
 *     "exito": true,
 *     "mensaje": "Producto eliminado correctamente"
 * }
 */

require_once 'config.php';

// ============================================
// VERIFICAR QUE SE ENVIÓ EL ID
// ============================================
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Debes enviar el ID del producto a eliminar. Ejemplo: ?id=5'
    ]);
    exit();
}

// ============================================
// OBTENER Y VALIDAR ID
// ============================================
$id = intval($_GET['id']);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'El ID debe ser un número positivo'
    ]);
    exit();
}

// ============================================
// VERIFICAR QUE EL PRODUCTO EXISTE
// ============================================
$sqlVerificar = "SELECT nombre FROM productos WHERE id = $id";
$resultado = $conexion->query($sqlVerificar);

if ($resultado->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'El producto no existe'
    ]);
    exit();
}

// Guardar el nombre para el mensaje
$producto = $resultado->fetch_assoc();
$nombreProducto = $producto['nombre'];

// ============================================
// ELIMINAR EL PRODUCTO
// ============================================
$sql = "DELETE FROM productos WHERE id = $id";

if ($conexion->query($sql)) {
    echo json_encode([
        'exito' => true,
        'mensaje' => "Producto '$nombreProducto' eliminado correctamente",
        'id_eliminado' => $id
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error al eliminar el producto',
        'error' => $conexion->error
    ]);
}

$conexion->close();
?>
