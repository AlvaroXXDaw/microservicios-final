-- ============================================
-- BASE DE DATOS: TIENDA ONLINE
-- ============================================
-- Proyecto: Sistema de gestión de tienda online
-- Fecha: 2026
-- Script COMPLETO Y UNIFICADO
-- Solo ejecuta este archivo para tener todo listo
-- ============================================

CREATE DATABASE IF NOT EXISTS tienda_online 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE tienda_online;

-- ============================================
-- TABLA: USUARIOS
-- ============================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('empleado', 'jefe') DEFAULT 'empleado',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_rol (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: PRODUCTOS
-- ============================================
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    categoria VARCHAR(50) NOT NULL DEFAULT 'General',
    precio DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    imagen VARCHAR(255) DEFAULT 'https://via.placeholder.com/300',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre),
    INDEX idx_stock (stock),
    INDEX idx_categoria (categoria),
    CHECK (precio > 0),
    CHECK (stock >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: CARRITO
-- ============================================
CREATE TABLE IF NOT EXISTS carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    fecha_agregado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (usuario_id, producto_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: FACTURAS
-- ============================================
CREATE TABLE IF NOT EXISTS facturas (
    factura_id VARCHAR(50) PRIMARY KEY,
    usuario_id INT NOT NULL,
    direccion_envio VARCHAR(255) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    envio DECIMAL(10, 2) NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    fecha_factura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_factura_usuario (usuario_id),
    INDEX idx_factura_fecha (fecha_factura),
    CHECK (subtotal >= 0),
    CHECK (envio >= 0),
    CHECK (total >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: COMPRAS
-- ============================================
CREATE TABLE IF NOT EXISTS compras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    fecha_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    factura_id VARCHAR(50) DEFAULT NULL,
    factura_url VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE,
    FOREIGN KEY (factura_id) REFERENCES facturas(factura_id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha_compra),
    INDEX idx_factura (factura_id),
    CHECK (cantidad > 0),
    CHECK (precio_unitario > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- USUARIOS POR DEFECTO
-- ============================================
-- admin@tienda.com / admin123
-- empleado@tienda.com / admin123
-- PepePepe@gmail.com / PepePepe
-- ============================================
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Administrador', 'admin@tienda.com', 'admin123', 'jefe'),
('Empleado Test', 'empleado@tienda.com', 'admin123', 'empleado'),
('Pepe', 'PepePepe@gmail.com', 'PepePepe', 'empleado');

-- ============================================
-- PRODUCTOS
-- ============================================
INSERT INTO productos (nombre, descripcion, categoria, precio, stock, imagen) VALUES 
('Laptop HP Pavilion', 'Laptop HP Pavilion 15.6", Intel Core i5, 8GB RAM, 256GB SSD', 'Ordenadores', 599.99, 15, 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=500&q=80'),
('Mouse Logitech MX', 'Mouse inalámbrico ergonómico Logitech MX Master 3', 'Periféricos', 89.99, 50, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=500&q=80'),
('Teclado Mecánico RGB', 'Teclado mecánico gaming con iluminación RGB, switches azules', 'Periféricos', 129.99, 30, 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=500&q=80'),
('Monitor Samsung 27"', 'Monitor Samsung 27 pulgadas, Full HD, 144Hz, panel IPS', 'Ordenadores', 249.99, 20, 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=500&q=80'),
('Auriculares Sony', 'Auriculares inalámbricos Sony con cancelación de ruido', 'Audio', 179.99, 25, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&q=80'),
('Webcam Logitech C920', 'Webcam Full HD 1080p con micrófono estéreo integrado', 'Periféricos', 79.99, 40, 'https://images.unsplash.com/photo-1587826080692-f439cd0b70da?w=500&q=80'),
('iPad Pro 12.9"', 'Apple iPad Pro con chip M2 y pantalla XDR', 'Tablets', 1099.00, 15, 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=500&q=80'),
('Samsung Galaxy Tab S9', 'Tablet Android de alta gama con S Pen incluido', 'Tablets', 899.00, 20, 'https://images.unsplash.com/photo-1632634133474-d8e64a3476f0?w=500&q=80'),
('Xiaomi Pad 6', 'Tablet calidad-precio con pantalla de 144Hz', 'Tablets', 399.00, 30, 'https://images.unsplash.com/photo-1561154464-82e9adf32764?w=500&q=80'),
('Lenovo Tab P12', 'Tablet perfecta para estudiantes y multimedia', 'Tablets', 349.00, 25, 'https://images.unsplash.com/photo-1585790050230-5dd28404ccb9?w=500&q=80'),
('Microsoft Surface Pro 9', 'Portátil y tablet 2 en 1 con Windows 11', 'Tablets', 1299.00, 10, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&q=80'),
('iPad Air 5', 'Potencia y ligereza con el chip M1', 'Tablets', 769.00, 18, 'https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?w=500&q=80'),
('Samsung Galaxy Tab A8', 'Tablet económica para toda la familia', 'Tablets', 199.00, 50, 'https://images.unsplash.com/photo-1623126908029-58cb08a2b272?w=500&q=80'),
('Huawei MatePad 11.5', 'Pantalla FullView y gran autonomía', 'Tablets', 299.00, 22, 'https://images.unsplash.com/photo-1628815113969-0487917f7a58?w=500&q=80'),
('Amazon Fire Max 11', 'La tablet más potente de Amazon', 'Tablets', 269.00, 35, 'https://images.unsplash.com/photo-1611532736597-de2d4265fba3?w=500&q=80'),
('Realme Pad 2', 'Gran pantalla y batería de larga duración', 'Tablets', 249.00, 28, 'https://images.unsplash.com/photo-1542751110-97427bbecf20?w=500&q=80'),
('Apple Watch Series 9', 'El reloj más avanzado de Apple con doble toque', 'Smartwatch', 449.00, 25, 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=500&q=80'),
('Samsung Galaxy Watch 6', 'Monitorización de salud avanzada y diseño elegante', 'Smartwatch', 299.00, 30, 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=500&q=80'),
('Garmin Fenix 7', 'Reloj GPS multideporte definitivo', 'Smartwatch', 699.00, 12, 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=500&q=80'),
('Xiaomi Smart Band 8', 'La pulsera de actividad más popular', 'Smartwatch', 49.00, 100, 'https://images.unsplash.com/photo-1575311373937-040b8e1fd5b6?w=500&q=80'),
('Fitbit Versa 4', 'Smartwatch enfocado en fitness y salud', 'Smartwatch', 199.00, 40, 'https://images.unsplash.com/photo-1557438159-51eec7a6c9e8?w=500&q=80'),
('Amazfit GTR 4', 'Diseño clásico con funciones deportivas modernas', 'Smartwatch', 169.00, 35, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&q=80'),
('Huawei Watch GT 4', 'Estilo geométrico y batería de 2 semanas', 'Smartwatch', 249.00, 20, 'https://images.unsplash.com/photo-1617043786394-f977fa12eddf?w=500&q=80'),
('Pixel Watch 2', 'La experiencia de Google en tu muñeca', 'Smartwatch', 399.00, 15, 'https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?w=500&q=80'),
('Polar Vantage V3', 'Reloj deportivo premium con biosensores', 'Smartwatch', 599.00, 8, 'https://images.unsplash.com/photo-1510017803434-a899398421b3?w=500&q=80'),
('Suunto Race', 'Pantalla AMOLED y mapas offline', 'Smartwatch', 449.00, 10, 'https://images.unsplash.com/photo-1544117519-31a4b719223d?w=500&q=80'),
('MacBook Air M2', 'Portátil ultraligero y potente', 'Ordenadores', 1199.00, 15, 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500&q=80'),
('Asus ROG Strix', 'Portátil gaming de alto rendimiento', 'Ordenadores', 1599.00, 8, 'https://images.unsplash.com/photo-1593642702821-c8da6771f0c6?w=500&q=80'),
('HP Omen 16', 'Diseñado para jugar sin límites', 'Ordenadores', 1399.00, 10, 'https://images.unsplash.com/photo-1618424181497-157f25b6ddd5?w=500&q=80'),
('Dell XPS 13', 'El ultrabook definitivo con pantalla InfinityEdge', 'Ordenadores', 1499.00, 12, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=500&q=80'),
('Lenovo Legion 5', 'Equilibrio perfecto entre rendimiento y precio', 'Ordenadores', 1099.00, 20, 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=500&q=80'),
('MSI Raider GE78', 'Potencia bruta para creadores y gamers', 'Ordenadores', 2499.00, 5, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?w=500&q=80'),
('Acer Swift Go', 'Portátil ligero con pantalla OLED', 'Ordenadores', 899.00, 18, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500&q=80'),
('LG Gram 17', 'El portátil de 17 pulgadas más ligero', 'Ordenadores', 1699.00, 7, 'https://images.unsplash.com/photo-1611078489935-0cb964de46d6?w=500&q=80'),
('Mac Mini M2', 'Pequeño pero matón', 'Ordenadores', 699.00, 30, 'https://images.unsplash.com/photo-1637410124884-12a684746a24?w=500&q=80'),
('iMac 24"', 'Todo en uno con diseño colorido', 'Ordenadores', 1599.00, 10, 'https://images.unsplash.com/photo-1527443060795-0402a18906c3?w=500&q=80'),
('Logitech G Pro X', 'Teclado mecánico gaming TKL', 'Periféricos', 129.00, 25, 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&q=80'),
('Razer DeathAdder V3', 'Ratón ergonómico ultraligero', 'Periféricos', 79.99, 40, 'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?w=500&q=80'),
('Corsair K70 RGB', 'Teclado mecánico premium', 'Periféricos', 169.99, 15, 'https://images.unsplash.com/photo-1595225476474-87563907a212?w=500&q=80'),
('HyperX QuadCast S', 'Micrófono USB con iluminación RGB', 'Periféricos', 159.00, 20, 'https://images.unsplash.com/photo-1598550476439-6847785fcea6?w=500&q=80'),
('Elgato Stream Deck', 'Controlador de contenido para streamers', 'Periféricos', 149.00, 30, 'https://images.unsplash.com/photo-1625842268584-8f3296236761?w=500&q=80'),
('Logitech MX Master 3S', 'El ratón definitivo para productividad', 'Periféricos', 109.00, 35, 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=500&q=80'),
('Monitor LG UltraGear', 'Monitor gaming 27" 144Hz IPS', 'Periféricos', 299.00, 15, 'https://images.unsplash.com/photo-1585792180666-f7347c490ee2?w=500&q=80'),
('BenQ ScreenBar', 'Lámpara para monitor que cuida tus ojos', 'Periféricos', 109.00, 10, 'https://images.unsplash.com/photo-1513506003901-1e6a229e2d15?w=500&q=80'),
('Wacom Intuos Pro', 'Tableta gráfica profesional', 'Periféricos', 349.00, 8, 'https://images.unsplash.com/photo-1563206767-5b18f218e8de?w=500&q=80'),
('Keychron Q1 Pro', 'Teclado mecánico custom inalámbrico', 'Periféricos', 199.00, 12, 'https://images.unsplash.com/photo-1595044426077-d36d9236d54a?w=500&q=80'),
('Sony WH-1000XM5', 'La mejor cancelación de ruido del mercado', 'Audio', 349.00, 20, 'https://images.unsplash.com/photo-1618366712010-f4ae9c647dcb?w=500&q=80'),
('AirPods Pro 2', 'Sonido mágico con cancelación activa', 'Audio', 279.00, 40, 'https://images.unsplash.com/photo-1606220588913-b3aacb4d2f46?w=500&q=80'),
('Bose QuietComfort 45', 'Comodidad legendaria y sonido premium', 'Audio', 299.00, 15, 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=500&q=80'),
('JBL Flip 6', 'Altavoz Bluetooth portátil resistente al agua', 'Audio', 119.00, 50, 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=500&q=80'),
('Sonos Era 100', 'Altavoz inteligente con sonido estéreo', 'Audio', 279.00, 10, 'https://images.unsplash.com/photo-1545454675-3531b543be5d?w=500&q=80'),
('Marshall Major IV', 'Diseño icónico y 80 horas de batería', 'Audio', 129.00, 25, 'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=500&q=80'),
('Sennheiser Momentum 4', 'Calidad de audio audiófila inalámbrica', 'Audio', 329.00, 12, 'https://images.unsplash.com/photo-1599669454699-248893623440?w=500&q=80'),
('Nothing Ear (2)', 'Diseño transparente y sonido personalizado', 'Audio', 149.00, 30, 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=500&q=80'),
('Audio-Technica AT2020', 'Micrófono de condensador cardioide', 'Audio', 99.00, 20, 'https://images.unsplash.com/photo-1598550476439-6847785fcea6?w=500&q=80'),
('Google Pixel Buds Pro', 'Sonido premium de Google', 'Audio', 199.00, 25, 'https://images.unsplash.com/photo-1655212738632-b1d08b04cdce?w=500&q=80');

-- ============================================
-- FIN DEL SCRIPT
-- ============================================
-- Credenciales:
-- Admin: admin@tienda.com / admin123
-- Empleado: empleado@tienda.com / admin123
-- ============================================
