<?php
/**
 * ============================================
 * CREAR PRODUCTO (Solo Admin)
 * ============================================
 * 
 * Este archivo permite añadir nuevos productos a la tienda.
 * Solo debería usarse por usuarios con rol "jefe".
 * 
 * MÉTODO: POST
 * 
 * CAMPOS OBLIGATORIOS:
 * - nombre (texto)
 * - precio (número decimal)
 * - stock (número entero)
 * 
 * CAMPOS OPCIONALES:
 * - descripcion (texto)
 * - imagen (URL de imagen)
 * 
 * EJEMPLO DE USO:
 * 
 * fetch('http://localhost/DWEC/Angular/ProyectoMio/backend/crear_producto.php', {
 *     method: 'POST',
 *     headers: { 'Content-Type': 'application/json' },
 *     body: JSON.stringify({
 *         nombre: 'Teclado Gaming',
 *         descripcion: 'Teclado mecánico con luces RGB',
 *         precio: 89.99,
 *         stock: 25,
 *         imagen: 'https://ejemplo.com/teclado.jpg'
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
// VALIDAR CAMPOS OBLIGATORIOS
// ============================================
if (!isset($datos['nombre']) || !isset($datos['precio']) || !isset($datos['stock'])) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Campos obligatorios: nombre, precio, stock'
    ]);
    exit();
}

// ============================================
// LIMPIAR Y PREPARAR DATOS
// ============================================
$nombre = $conexion->real_escape_string(trim($datos['nombre']));
$descripcion = isset($datos['descripcion']) ? $conexion->real_escape_string($datos['descripcion']) : '';
$precio = floatval($datos['precio']); // Convertir a número decimal
$stock = intval($datos['stock']);      // Convertir a número entero
$imagen = isset($datos['imagen']) ? $conexion->real_escape_string($datos['imagen']) : 'https://via.placeholder.com/300';

// ============================================
// VALIDAR DATOS
// ============================================
if (empty($nombre)) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'El nombre no puede estar vacío'
    ]);
    exit();
}

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
// INSERTAR PRODUCTO EN LA BASE DE DATOS
// ============================================
$sql = "INSERT INTO productos (nombre, descripcion, precio, stock, imagen) 
        VALUES ('$nombre', '$descripcion', $precio, $stock, '$imagen')";

if ($conexion->query($sql)) {
    $nuevoId = $conexion->insert_id;
    
    echo json_encode([
        'exito' => true,
        'mensaje' => 'Producto creado correctamente',
        'producto' => [
            'id' => $nuevoId,
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
        'mensaje' => 'Error al crear el producto',
        'error' => $conexion->error
    ]);
}

$conexion->close();
?>
