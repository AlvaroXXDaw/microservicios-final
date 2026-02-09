<?php
/**
 * Script para crear usuario admin
 * Ejecutar una vez desde el navegador: http://localhost/DWEC/Angular/ProyectoMio/backend/crear_admin.php
 */

require_once 'config.php';

// Datos del nuevo admin
$nombre = 'SuperAdmin';
$email = 'superadmin@tienda.com';
$password = 'admin123';
$rol = 'jefe';

// Guardar contraseña en texto plano (solo pruebas)
$passwordPlano = $conexion->real_escape_string($password);

// Primero eliminamos si existe
$conexion->query("DELETE FROM usuarios WHERE email = '$email'");

// Insertar nuevo usuario
$sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('$nombre', '$email', '$passwordPlano', '$rol')";

if ($conexion->query($sql)) {
    echo json_encode([
        'exito' => true,
        'mensaje' => 'Usuario admin creado correctamente',
        'email' => $email,
        'password' => $password
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        'exito' => false,
        'error' => $conexion->error
    ]);
}

$conexion->close();
?>
