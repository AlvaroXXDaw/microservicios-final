<?php
/**
 * Configuración de la Base de Datos y CORS
 * Tienda Online - Backend PHP
 */

// ============================================
// INICIAR SESIÓN PHP
// ============================================
session_start();

// ============================================
// CONFIGURACIÓN DE LA BASE DE DATOS
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tienda_online');

// ============================================
// CREAR CONEXIÓN A LA BASE DE DATOS
// ============================================
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar conexión
if ($conexion->connect_error) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'error' => 'Error de conexión a la base de datos',
        'details' => $conexion->connect_error
    ]));
}

// Establecer charset UTF-8
$conexion->set_charset("utf8mb4");

// ============================================
// CONFIGURACIÓN DE HEADERS CORS
// ============================================
// IMPORTANTE: Para usar credentials (cookies), Origin NO puede ser *
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// ============================================
// MANEJAR PETICIONES PREFLIGHT (OPTIONS)
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
