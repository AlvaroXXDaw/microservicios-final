<?php
/**
 * BUSCAR PRODUCTOS
 * Busca productos por ID o nombre
 * 
 * MÉTODO: GET
 * PARÁMETROS:
 * - id: buscar por ID específico
 * - nombre: buscar por nombre (LIKE)
 */

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Este endpoint solo acepta GET'
    ]);
    exit();
}

// Buscar por ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql = "SELECT id, nombre, descripcion, precio, stock, imagen, fecha_creacion 
            FROM productos 
            WHERE id = $id";
    
    $resultado = $conexion->query($sql);
    
    if ($resultado->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'exito' => false,
            'mensaje' => 'Producto no encontrado'
        ]);
        exit();
    }
    
    $producto = $resultado->fetch_assoc();
    
    echo json_encode([
        'exito' => true,
        'producto' => $producto
    ]);
    exit();
}

// Buscar por nombre
if (isset($_GET['nombre'])) {
    $nombre = $_GET['nombre'];
    $nombre_like = '%' . $conexion->real_escape_string($nombre) . '%';
    
    $sql = "SELECT id, nombre, descripcion, precio, stock, imagen, fecha_creacion 
            FROM productos 
            WHERE nombre LIKE '$nombre_like' 
            OR descripcion LIKE '$nombre_like'
            ORDER BY nombre ASC
            LIMIT 10";
    
    $resultado = $conexion->query($sql);
    
    $productos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }
    
    echo json_encode([
        'exito' => true,
        'productos' => $productos,
        'total' => count($productos)
    ]);
    exit();
}

// Si no se proporciona ni id ni nombre
echo json_encode([
    'exito' => false,
    'mensaje' => 'Debes proporcionar "id" o "nombre" como parámetro'
]);

$conexion->close();
?>
