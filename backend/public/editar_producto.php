<?php
/**
 * ============================================
 * EDITAR PRODUCTO (Solo Admin)
 * ============================================
 * 
 * Este archivo permite modificar productos existentes.
 * 
 * MÉTODO: POST (usamos POST en lugar de PUT por simplicidad)
 * 
 * CAMPOS OBLIGATORIOS:
 * - id (ID del producto a editar)
 * - nombre
 * - precio
 * - stock
 * 
 * EJEMPLO DE USO:
 * 
 * fetch('http://localhost/DWEC/Angular/ProyectoMio/backend/editar_producto.php', {
 *     method: 'POST',
 *     headers: { 'Content-Type': 'application/json' },
 *     body: JSON.stringify({
 *         id: 1,
 *         nombre: 'Laptop HP Actualizada',
 *         descripcion: 'Nueva descripción',
 *         precio: 649.99,
 *         stock: 20,
 *         imagen: 'https://ejemplo.com/laptop-nueva.jpg'
 *     })
 * })
 * .then(response => response.json())
 * .then(data => console.log(data));
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
// VALIDAR CAMPO ID (obligatorio para editar)
// ============================================
if (!isset($datos['id'])) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Debes enviar el ID del producto a editar'
    ]);
    exit();
}

// ============================================
// VALIDAR OTROS CAMPOS OBLIGATORIOS
// ============================================
if (!isset($datos['nombre']) || !isset($datos['precio']) || !isset($datos['stock'])) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Campos obligatorios: id, nombre, precio, stock'
    ]);
    exit();
}

// ============================================
// LIMPIAR Y PREPARAR DATOS
// ============================================
$id = intval($datos['id']);
$nombre = $conexion->real_escape_string(trim($datos['nombre']));
$descripcion = isset($datos['descripcion']) ? $conexion->real_escape_string($datos['descripcion']) : '';
$precio = floatval($datos['precio']);
$stock = intval($datos['stock']);
$imagen = isset($datos['imagen']) ? $conexion->real_escape_string($datos['imagen']) : '';

// ============================================
// VALIDAR DATOS
// ============================================
if ($precio <= 0) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'El precio debe ser mayor que 0'
    ]);
    exit();
}

if ($stock < 0) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'El stock no puede ser negativo'
    ]);
    exit();
}

// ============================================
// VERIFICAR QUE EL PRODUCTO EXISTE
// ============================================
$sqlVerificar = "SELECT id FROM productos WHERE id = $id";
$resultado = $conexion->query($sqlVerificar);

if ($resultado->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'El producto no existe'
    ]);
    exit();
}

// ============================================
// ACTUALIZAR PRODUCTO
// ============================================
$sql = "UPDATE productos 
        SET nombre = '$nombre',
            descripcion = '$descripcion',
            precio = $precio,
            stock = $stock,
            imagen = '$imagen'
        WHERE id = $id";

if ($conexion->query($sql)) {
    echo json_encode([
        'exito' => true,
        'mensaje' => 'Producto actualizado correctamente',
        'producto' => [
            'id' => $id,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'stock' => $stock,
            'imagen' => $imagen
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error al actualizar el producto',
        'error' => $conexion->error
    ]);
}

$conexion->close();
?>
