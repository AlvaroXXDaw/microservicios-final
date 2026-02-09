<?php
/**
 * API de Autenticación - Tienda Online
 * 
 * Endpoints:
 * - POST /auth.php (action: login) - Iniciar sesión
 * - POST /auth.php (action: register) - Registrar nuevo usuario
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $action = isset($data['action']) ? $data['action'] : '';
        
        // ============================================
        // LOGIN - Iniciar sesión
        // ============================================
        if ($action === 'login') {
            $email = $conexion->real_escape_string($data['email']);
            $password = $data['password'];
            
            // Buscar usuario por email
            $sql = "SELECT id, nombre, email, password, rol, fecha_registro 
                    FROM usuarios 
                    WHERE email = '$email'";
            $result = $conexion->query($sql);
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Verificar contraseña
                if ($password === $user['password']) {
                    // No enviar el password en la respuesta
                    unset($user['password']);
                    
                    echo json_encode([
                        'success' => true,
                        'user' => $user,
                        'message' => 'Login exitoso'
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Credenciales inválidas'
                    ]);
                }
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Usuario no encontrado'
                ]);
            }
        } 
        // ============================================
        // REGISTER - Registrar nuevo usuario
        // ============================================
        elseif ($action === 'register') {
            $nombre = $conexion->real_escape_string($data['nombre']);
            $email = $conexion->real_escape_string($data['email']);
            $password = $conexion->real_escape_string($data['password']);
            $rol = isset($data['rol']) ? $data['rol'] : 'empleado';
            
            // Verificar si el email ya existe
            $checkSql = "SELECT id FROM usuarios WHERE email = '$email'";
            $checkResult = $conexion->query($checkSql);
            
            if ($checkResult->num_rows > 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'El email ya está registrado'
                ]);
            } else {
                // Insertar nuevo usuario
                $sql = "INSERT INTO usuarios (nombre, email, password, rol) 
                        VALUES ('$nombre', '$email', '$password', '$rol')";
                
                if ($conexion->query($sql)) {
                    $userId = $conexion->insert_id;
                    
                    echo json_encode([
                        'success' => true,
                        'user' => [
                            'id' => $userId,
                            'nombre' => $nombre,
                            'email' => $email,
                            'rol' => $rol
                        ],
                        'message' => 'Usuario registrado exitosamente'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Error al registrar usuario: ' . $conexion->error
                    ]);
                }
            }
        } 
        else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Acción no válida. Use "login" o "register"'
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido. Use POST'
        ]);
        break;
}

$conexion->close();
?>
