<?php
/**
 * ============================================
 * REGISTRO - Crear Nuevo Usuario
 * ============================================
 * 
 * Este archivo permite registrar nuevos usuarios.
 * 
 * CÓMO FUNCIONA:
 * 1. Recibe nombre, email y contraseña en JSON
 * 2. Verifica que el email no esté ya registrado
 * 3. Guarda el usuario con la contraseña hasheada
 * 
 * MÉTODO: POST
 * 
 * EJEMPLO DE USO DESDE JAVASCRIPT:
 * 
 * fetch('http://localhost/DWEC/Angular/ProyectoMio/backend/registro.php', {
 *     method: 'POST',
 *     headers: { 'Content-Type': 'application/json' },
 *     body: JSON.stringify({
 *         nombre: 'Juan García',
 *         email: 'juan@email.com',
 *         password: 'micontraseña123'
 *     })
 * })
 * .then(response => response.json())
 * .then(data => console.log(data));
 * 
 * RESPUESTA EXITOSA:
 * {
 *     "exito": true,
 *     "mensaje": "Usuario registrado correctamente",
 *     "usuario": {
 *         "id": 3,
 *         "nombre": "Juan García",
 *         "email": "juan@email.com",
 *         "rol": "empleado"
 *     }
 * }
 */

// Incluir configuración
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
// LEER DATOS DEL JSON
// ============================================
$datosJSON = file_get_contents('php://input');
$datos = json_decode($datosJSON, true);

// ============================================
// VALIDAR CAMPOS OBLIGATORIOS
// ============================================
if (!isset($datos['nombre']) || !isset($datos['email']) || !isset($datos['password'])) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Debes enviar nombre, email y password'
    ]);
    exit();
}

// ============================================
// LIMPIAR Y VALIDAR DATOS
// ============================================
$nombre = $conexion->real_escape_string(trim($datos['nombre']));
$email = $conexion->real_escape_string(trim($datos['email']));
$password = $datos['password'];
$passwordPlano = $conexion->real_escape_string($password);

// Validar que no estén vacíos
if (empty($nombre) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Todos los campos son obligatorios'
    ]);
    exit();
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'El email no tiene un formato válido'
    ]);
    exit();
}

// Validar longitud de contraseña
if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'La contraseña debe tener al menos 6 caracteres'
    ]);
    exit();
}

// ============================================
// VERIFICAR QUE EL EMAIL NO EXISTA
// ============================================
$sqlVerificar = "SELECT id FROM usuarios WHERE email = '$email'";
$resultado = $conexion->query($sqlVerificar);

if ($resultado->num_rows > 0) {
    http_response_code(400);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Este email ya está registrado'
    ]);
    exit();
}

// ============================================
// CONTRASEÑA EN TEXTO PLANO (SOLO PRUEBAS)
// ============================================
// OJO: esto es inseguro y solo debe usarse en entorno de pruebas
$passwordGuardar = $passwordPlano;

// ============================================
// INSERTAR USUARIO EN LA BASE DE DATOS
// ============================================
// Por defecto, los nuevos usuarios tienen rol 'empleado'
$sql = "INSERT INTO usuarios (nombre, email, password, rol) 
        VALUES ('$nombre', '$email', '$passwordGuardar', 'empleado')";

if ($conexion->query($sql)) {
    // Obtener el ID del usuario recién creado
    $nuevoId = $conexion->insert_id;
    
    echo json_encode([
        'exito' => true,
        'mensaje' => 'Usuario registrado correctamente',
        'usuario' => [
            'id' => $nuevoId,
            'nombre' => $nombre,
            'email' => $email,
            'rol' => 'empleado'
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Error al registrar usuario',
        'error' => $conexion->error
    ]);
}

$conexion->close();
?>
