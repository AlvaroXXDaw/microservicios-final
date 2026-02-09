<?php
/**
 * API de Productos - Tienda Online
 * 
 * Endpoints:
 * - GET /productos.php - Obtener todos los productos
 * - GET /productos.php?id=1 - Obtener un producto específico
 * - POST /productos.php - Crear nuevo producto (solo admin)
 * - PUT /productos.php - Actualizar producto (solo admin)
 * - DELETE /productos.php?id=1 - Eliminar producto (solo admin)
 */

require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    // ============================================
    // GET - Obtener productos
    // ============================================
    case 'GET':
        if (isset($_GET['id'])) {
            // Obtener un producto específico por ID
            $id = intval($_GET['id']);
            $sql = "SELECT id, nombre, descripcion, precio, stock, imagen, 
                           fecha_creacion, fecha_actualizacion 
                    FROM productos 
                    WHERE id = $id";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                $producto = $result->fetch_assoc();
                echo json_encode([
                    'success' => true,
                    'producto' => $producto
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Producto no encontrado'
                ]);
            }
        } else {
            // Obtener todos los productos
            $sql = "SELECT id, nombre, descripcion, precio, stock, imagen, 
                           fecha_creacion, fecha_actualizacion 
                    FROM productos 
                    ORDER BY fecha_creacion DESC";
            $result = $conn->query($sql);
            
            $productos = [];
            while($row = $result->fetch_assoc()) {
                $productos[] = $row;
            }
            
            echo json_encode([
                'success' => true,
                'productos' => $productos,
                'total' => count($productos)
            ]);
        }
        break;
        
    // ============================================
    // POST - Crear nuevo producto
    // ============================================
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar campos obligatorios
        if (!isset($data['nombre']) || !isset($data['precio']) || !isset($data['stock'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Faltan campos obligatorios: nombre, precio, stock'
            ]);
            break;
        }
        
        $nombre = $conn->real_escape_string($data['nombre']);
        $descripcion = isset($data['descripcion']) ? $conn->real_escape_string($data['descripcion']) : '';
        $precio = floatval($data['precio']);
        $stock = intval($data['stock']);
        $imagen = isset($data['imagen']) ? $conn->real_escape_string($data['imagen']) : 'https://via.placeholder.com/300';
        
        // Validar que el precio y stock sean positivos
        if ($precio <= 0 || $stock < 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'El precio debe ser mayor a 0 y el stock no puede ser negativo'
            ]);
            break;
        }
        
        $sql = "INSERT INTO productos (nombre, descripcion, precio, stock, imagen) 
                VALUES ('$nombre', '$descripcion', $precio, $stock, '$imagen')";
        
        if ($conn->query($sql)) {
            $productoId = $conn->insert_id;
            
            echo json_encode([
                'success' => true,
                'id' => $productoId,
                'message' => 'Producto creado exitosamente',
                'producto' => [
                    'id' => $productoId,
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'precio' => $precio,
                    'stock' => $stock,
                    'imagen' => $imagen
                ]
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al crear producto: ' . $conn->error
            ]);
        }
        break;
        
    // ============================================
    // PUT - Actualizar producto
    // ============================================
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'ID del producto es obligatorio'
            ]);
            break;
        }
        
        $id = intval($data['id']);
        $nombre = $conn->real_escape_string($data['nombre']);
        $descripcion = $conn->real_escape_string($data['descripcion']);
        $precio = floatval($data['precio']);
        $stock = intval($data['stock']);
        $imagen = isset($data['imagen']) ? $conn->real_escape_string($data['imagen']) : '';
        
        // Validar que el precio y stock sean válidos
        if ($precio <= 0 || $stock < 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'El precio debe ser mayor a 0 y el stock no puede ser negativo'
            ]);
            break;
        }
        
        $sql = "UPDATE productos 
                SET nombre='$nombre', 
                    descripcion='$descripcion', 
                    precio=$precio, 
                    stock=$stock, 
                    imagen='$imagen' 
                WHERE id=$id";
        
        if ($conn->query($sql)) {
            if ($conn->affected_rows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto actualizado exitosamente',
                    'producto' => [
                        'id' => $id,
                        'nombre' => $nombre,
                        'descripcion' => $descripcion,
                        'precio' => $precio,
                        'stock' => $stock,
                        'imagen' => $imagen
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'No se realizaron cambios (datos iguales)'
                ]);
            }
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al actualizar producto: ' . $conn->error
            ]);
        }
        break;
        
    // ============================================
    // DELETE - Eliminar producto
    // ============================================
    case 'DELETE':
        if (!isset($_GET['id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'ID del producto es obligatorio'
            ]);
            break;
        }
        
        $id = intval($_GET['id']);
        
        // Verificar que el producto existe antes de eliminar
        $checkSql = "SELECT nombre FROM productos WHERE id=$id";
        $checkResult = $conn->query($checkSql);
        
        if ($checkResult->num_rows === 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Producto no encontrado'
            ]);
            break;
        }
        
        $sql = "DELETE FROM productos WHERE id=$id";
        
        if ($conn->query($sql)) {
            echo json_encode([
                'success' => true,
                'message' => 'Producto eliminado exitosamente',
                'id' => $id
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al eliminar producto: ' . $conn->error
            ]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido. Use GET, POST, PUT o DELETE'
        ]);
        break;
}

$conn->close();
?>
