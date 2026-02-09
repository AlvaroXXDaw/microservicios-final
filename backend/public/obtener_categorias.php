<?php
/**
 * ============================================
 * OBTENER CATEGORÍAS
 * ============================================
 * 
 * Este archivo devuelve la lista de categorías únicas.
 * 
 * MÉTODO: GET
 * 
 * RESPUESTA:
 * {
 *   "exito": true,
 *   "categorias": ["Ordenadores", "Periféricos", "Audio"]
 * }
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
// OBTENER CATEGORÍAS ÚNICAS
// ============================================
$sql = "SELECT DISTINCT categoria FROM productos ORDER BY categoria ASC";
$resultado = $conexion->query($sql);

$categorias = [];
while ($fila = $resultado->fetch_assoc()) {
    $categorias[] = $fila['categoria'];
}

echo json_encode([
    'exito' => true,
    'categorias' => $categorias,
    'total' => count($categorias)
]);

$conexion->close();
?>
