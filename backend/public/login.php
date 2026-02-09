<?php
/**
 * ============================================
 * LOGIN - Iniciar Sesión
 * ============================================
 * 
 * Este archivo maneja el inicio de sesión de usuarios.
 * 
 * CÓMO FUNCIONA:
 * 1. Recibe email y contraseña en formato JSON
 * 2. Busca el usuario en la base de datos
 * 3. Verifica que la contraseña sea correcta
 * 4. Si todo está bien, devuelve los datos del usuario
 * 
 * MÉTODO: POST
 * 
 * EJEMPLO DE USO DESDE JAVASCRIPT:
 * 
 * fetch('http://localhost/DWEC/Angular/ProyectoMio/backend/login.php', {
 *     method: 'POST',
 *     headers: { 'Content-Type': 'application/json' },
 *     body: JSON.stringify({
 *         email: 'admin@tienda.com',
 *         password: 'admin123'
 *     })
 * })
 * .then(response => response.json())
 * .then(data => console.log(data));
 * 
 * RESPUESTA EXITOSA:
 * {
 *     "exito": true,
 *     "mensaje": "Login correcto",
 *     "usuario": {
 *         "id": 1,
 *         "nombre": "Administrador",
 *         "email": "admin@tienda.com",
 *         "rol": "jefe"
 *     }
 * }
 * 
 * RESPUESTA ERROR:
 * {
 *     "exito": false,
 *     "mensaje": "Email o contraseña incorrectos"
 * }
 */

// Incluir el archivo de configuración (conexión a BD)
require_once 'config.php';

// ============================================
// VERIFICAR QUE SEA MÉTODO POST
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // 405 = Método no permitido
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Este endpoint solo acepta POST'
    ]);
    exit();
}

// ============================================
// LEER DATOS ENVIADOS EN JSON
// ============================================
// file_get_contents('php://input') lee el body de la petición
// json_decode lo convierte de JSON a array de PHP
$datosJSON = file_get_contents('php://input');
$datos = json_decode($datosJSON, true);

// ============================================
// VERIFICAR QUE SE ENVIARON EMAIL Y PASSWORD
// ============================================
if (!isset($datos['email']) || !isset($datos['password'])) {
    http_response_code(400); // 400 = Petición incorrecta
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Debes enviar email y password'
    ]);
    exit();
}

// ============================================
// OBTENER Y LIMPIAR DATOS
// ============================================
// real_escape_string previene inyección SQL
$email = $conexion->real_escape_string($datos['email']);
$password = $datos['password']; // No escapamos el password porque no se usa en SQL

// ============================================
// BUSCAR USUARIO EN LA BASE DE DATOS
// ============================================
$sql = "SELECT id, nombre, email, password, rol, fecha_registro 
        FROM usuarios 
        WHERE email = '$email'";

$resultado = $conexion->query($sql);

// ============================================
// VERIFICAR SI EXISTE EL USUARIO
// ============================================
if ($resultado->num_rows === 0) {
    // No existe usuario con ese email
    http_response_code(401); // 401 = No autorizado
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Email o contraseña incorrectos'
    ]);
    exit();
}

// ============================================
// OBTENER DATOS DEL USUARIO
// ============================================
$usuario = $resultado->fetch_assoc();

// ============================================
// VERIFICAR CONTRASEÑA
// ============================================
// Modo pruebas: comparamos directamente la contraseña en texto plano
if ($password !== $usuario['password']) {
    // Contraseña incorrecta
    http_response_code(401);
    echo json_encode([
        'exito' => false,
        'mensaje' => 'Email o contraseña incorrectos'
    ]);
    exit();
}

// ============================================
// LOGIN CORRECTO - DEVOLVER DATOS DEL USUARIO
// ============================================
// Importante: NO devolvemos el password por seguridad
// IMPORTANTE: NO devolvemos el password por seguridad
unset($usuario['password']);

// GUARDAR EN SESIÓN PHP
$_SESSION['user'] = $usuario;
$_SESSION['user_id'] = $usuario['id'];

echo json_encode([
    'exito' => true,
    'mensaje' => 'Login correcto',
    'usuario' => $usuario
]);

// Cerrar conexión
$conexion->close();
?>
