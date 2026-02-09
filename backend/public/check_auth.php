<?php
/**
 * ============================================
 * CHECK AUTH - Verificar Sesión
 * ============================================
 */
require_once 'config.php';

// Verificar si hay usuario en sesión
if (isset($_SESSION['user'])) {
    echo json_encode([
        'exito' => true,
        'usuario' => $_SESSION['user']
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'No hay sesión activa'
    ]);
}
?>
