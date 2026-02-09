<?php
/**
 * ============================================
 * COMPRAR - Procesar compra
 * ============================================
 * 
 * Este archivo procesa una compra y reduce el stock.
 * 
 * MÉTODO: POST
 * 
 * BODY:
 * {
 *   "items": [
 *     { "id": 1, "cantidad": 2 },
 *     { "id": 3, "cantidad": 1 }
 *   ]
 * }
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
// OBTENER DATOS DEL BODY
// ============================================
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['items']) || !is_array($data['items'])) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Formato de datos inválido'
    ]);
    exit();
}

// ============================================
// PROCESAR CADA ITEM
// ============================================
$errors = [];
$success = [];

foreach ($data['items'] as $item) {
    $id = intval($item['id']);
    $cantidad = intval($item['cantidad']);
    
    // Verificar stock disponible
    $checkSql = "SELECT nombre, stock FROM productos WHERE id = $id";
    $result = $conexion->query($checkSql);
    
    if ($result->num_rows === 0) {
        $errors[] = "Producto ID $id no encontrado";
        continue;
    }
    
    $producto = $result->fetch_assoc();
    
    if ($producto['stock'] < $cantidad) {
        $errors[] = "Stock insuficiente para {$producto['nombre']}";
        continue;
    }
    
    // Reducir stock
    $updateSql = "UPDATE productos SET stock = stock - $cantidad WHERE id = $id";
    if ($conexion->query($updateSql)) {
        $success[] = $producto['nombre'];
    } else {
        $errors[] = "Error al actualizar {$producto['nombre']}";
    }
}

// ============================================
// RESPUESTA
// ============================================
if (count($errors) > 0 && count($success) === 0) {
    echo json_encode([
        'exito' => false,
        'mensaje' => implode(', ', $errors)
    ]);
} else {
    echo json_encode([
        'exito' => true,
        'mensaje' => '¡Compra realizada con éxito!',
        'productos' => $success,
        'errores' => $errors
    ]);
}

$conexion->close();
?>
