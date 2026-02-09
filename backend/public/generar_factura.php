<?php
/**
 * ============================================
 * GENERAR FACTURA - Guarda datos en la BD
 * ============================================
 * Devuelve un ID y una URL dinamica para ver la factura.
 */

require_once 'config.php';

// Verificar sesion
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'No has iniciado sesion'
    ]);
    exit();
}

// Leer datos
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Datos invalidos'
    ]);
    exit();
}

$items = isset($data['items']) ? $data['items'] : [];
$subtotal = isset($data['subtotal']) ? floatval($data['subtotal']) : 0;
$envio = isset($data['envio']) ? floatval($data['envio']) : 0;
$total = isset($data['total']) ? floatval($data['total']) : 0;
$direccion = isset($data['direccion']) ? trim($data['direccion']) : '';

if (!is_array($items) || count($items) === 0 || $direccion === '') {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Datos incompletos'
    ]);
    exit();
}

$usuarioId = intval($_SESSION['user_id']);
$facturaId = 'FAC-' . date('Ymd') . '-' . substr(uniqid(), -6);

// Guardar en BD
$stmt = $conexion->prepare(
    "INSERT INTO facturas (factura_id, usuario_id, direccion_envio, subtotal, envio, total)
     VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param(
    'sisddd',
    $facturaId,
    $usuarioId,
    $direccion,
    $subtotal,
    $envio,
    $total
);

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error al guardar la factura'
    ]);
    exit();
}

// URL publica de la factura (dinamica)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$facturaUrl = $scheme . '://' . $host . $basePath . '/factura.php?factura_id=' . $facturaId;

// Respuesta
echo json_encode([
    'exito' => true,
    'facturaId' => $facturaId,
    'facturaUrl' => $facturaUrl
]);
?>
