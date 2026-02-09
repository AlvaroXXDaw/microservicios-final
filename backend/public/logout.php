<?php
/**
 * ============================================
 * LOGOUT - Cerrar Sesión
 * ============================================
 */
require_once 'config.php';

// Limpiar la sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// Destruir la sesión
session_destroy();

echo json_encode([
    'exito' => true,
    'mensaje' => 'Sesión cerrada correctamente'
]);
?>
