<?php
/**
 * ============================================
 * OBTENER PRODUCTOS
 * ============================================
 * 
 * Este archivo devuelve la lista de productos.
 * 
 * MÉTODO: GET
 * 
 * OPCIONES:
 * 1. Sin parámetros → Devuelve TODOS los productos
 * 2. Con ?id=X → Devuelve UN producto específico
 * 
 * EJEMPLOS DE USO:
 * 
 * // Obtener todos los productos
 * fetch('http://localhost/DWEC/Angular/ProyectoMio/backend/obtener_productos.php')
 *     .then(response => response.json())
 *     .then(data => console.log(data.productos));
 * 
 * // Obtener un producto específico
 * fetch('http://localhost/DWEC/Angular/ProyectoMio/backend/obtener_productos.php?id=1')
 *     .then(response => response.json())
 *     .then(data => console.log(data.producto));
 */

require_once 'config.php';

// ============================================
// VERIFICAR MÉTODO GET
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Este endpoint solo acepta GET'
    ]);
    exit();
}

// ============================================
// OPCIÓN 1: OBTENER UN PRODUCTO ESPECÍFICO
// ============================================
if (isset($_GET['id'])) {
    // intval convierte a número entero (por seguridad)
    $id = intval($_GET['id']);
    
    $sql = "SELECT id, nombre, descripcion, categoria, precio, stock, imagen, fecha_creacion 
            FROM productos 
            WHERE id = $id";
    
    $resultado = $conexion->query($sql);
    
    if ($resultado->num_rows === 0) {
        http_response_code(404); // 404 = No encontrado
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
    
} elseif (isset($_GET['search'])) {
    $search = $conexion->real_escape_string($_GET['search']);
    $sql = "SELECT id, nombre, descripcion, categoria, precio, stock, imagen, fecha_creacion 
            FROM productos 
            WHERE nombre LIKE '%$search%' 
            ORDER BY fecha_creacion DESC";
    
    $resultado = $conexion->query($sql);
    
    $productos = [];
    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }
    
    echo json_encode([
        'exito' => true,
        'productos' => $productos
    ]);
} elseif (isset($_GET['categoria'])) {
    // ============================================
    // OPCIÓN 3: FILTRAR POR CATEGORÍA
    // ============================================
    $categoria = $conexion->real_escape_string($_GET['categoria']);
    
    $sql = "SELECT id, nombre, descripcion, categoria, precio, stock, imagen, fecha_creacion 
            FROM productos 
            WHERE categoria = '$categoria'
            ORDER BY fecha_creacion DESC";
    
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
} else {
    // ============================================
    // OPCIÓN 4: OBTENER TODOS LOS PRODUCTOS
    // ============================================
    $sql = "SELECT id, nombre, descripcion, categoria, precio, stock, imagen, fecha_creacion 
            FROM productos 
            ORDER BY fecha_creacion DESC";
    
    $resultado = $conexion->query($sql);
    
    // Crear array para guardar los productos
    $productos = [];
    
    // Recorrer cada fila del resultado
    while ($fila = $resultado->fetch_assoc()) {
        $productos[] = $fila;
    }
    
    echo json_encode([
        'exito' => true,
        'productos' => $productos,
        'total' => count($productos)
    ]);
}

$conexion->close();
?>
