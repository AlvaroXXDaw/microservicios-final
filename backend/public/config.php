<?php

/**
 * Configuración de la Base de Datos y CORS
 * Tienda Online - Backend PHP (Docker)
 */

// ============================================
// INICIAR SESIÓN PHP
// ============================================
session_start();

// ============================================
// CONFIGURACIÓN DE LA BASE DE DATOS (DOCKER)
// ============================================
// En Docker NO es localhost: el host es el nombre del servicio MySQL en compose: "db"
define('DB_HOST', 'db');
define('DB_USER', getenv('MYSQL_USER') ?: 'app');
define('DB_PASS', getenv('MYSQL_PASSWORD') ?: 'secret');
define('DB_NAME', getenv('MYSQL_DATABASE') ?: 'tienda_online');

// ============================================
// CONFIGURACIÓN DE HEADERS CORS
// ============================================
// Para no bloquearte con ngrok, dejamos origen dinámico si viene ORIGIN.
// Si no viene, permitimos todo sin credenciales.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: false');

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

// ============================================
// CREAR CONEXIÓN A LA BASE DE DATOS
// ============================================
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conexion->set_charset("utf8mb4");
    $conn = $conexion; // alias para compatibilidad
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión a la base de datos',
        'details' => $e->getMessage()
    ]);
    exit();
}
