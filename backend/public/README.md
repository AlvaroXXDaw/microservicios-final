# Backend - Tienda Online

## 🔧 Instalación y Configuración

### 1. Iniciar XAMPP
Asegúrate de tener **Apache** y **MySQL** en ejecución.

### 2. Crear la Base de Datos
Ejecuta el archivo `database.sql` en phpMyAdmin o desde consola:

```bash
# Desde MySQL consola
mysql -u root -p < database.sql
```

O abre phpMyAdmin (http://localhost/phpmyadmin) y:
1. Crea una nueva base de datos llamada `tienda_online`
2. Importa el archivo `database.sql`

### 3. Verificar Configuración
El archivo `config.php` está configurado con:
- Host: `localhost`
- Usuario: `root`
- Password: `` (vacío)
- Base de datos: `tienda_online`

**Si tus credenciales de MySQL son diferentes, edita `config.php`**

## 👥 Usuarios de Prueba

Después de ejecutar `database.sql`, tendrás estos usuarios en la base de datos:

| Email | Password | Rol |
|-------|----------|-----|
| admin@tienda.com | admin123 | jefe |
| empleado@tienda.com | admin123 | empleado |

## 📡 Endpoints Disponibles

### Autenticación (auth.php)

**Login**
```http
POST http://localhost/DWEC/Angular/ProyectoMio/backend/auth.php
Content-Type: application/json

{
  "action": "login",
  "email": "admin@tienda.com",
  "password": "admin123"
}
```

**Registro**
```http
POST http://localhost/DWEC/Angular/ProyectoMio/backend/auth.php
Content-Type: application/json

{
  "action": "register",
  "nombre": "Nuevo Usuario",
  "email": "usuario@test.com",
  "password": "mipassword",
  "rol": "empleado"
}
```

### Productos (productos.php)

**Obtener todos los productos**
```http
GET http://localhost/DWEC/Angular/ProyectoMio/backend/productos.php
```

**Obtener un producto por ID**
```http
GET http://localhost/DWEC/Angular/ProyectoMio/backend/productos.php?id=1
```

**Crear producto** (requiere rol: jefe)
```http
POST http://localhost/DWEC/Angular/ProyectoMio/backend/productos.php
Content-Type: application/json

{
  "nombre": "Nuevo Producto",
  "descripcion": "Descripción del producto",
  "precio": 99.99,
  "stock": 20,
  "imagen": "https://via.placeholder.com/300"
}
```

**Actualizar producto** (requiere rol: jefe)
```http
PUT http://localhost/DWEC/Angular/ProyectoMio/backend/productos.php
Content-Type: application/json

{
  "id": 1,
  "nombre": "Producto Actualizado",
  "descripcion": "Nueva descripción",
  "precio": 149.99,
  "stock": 15,
  "imagen": "https://via.placeholder.com/300"
}
```

**Eliminar producto** (requiere rol: jefe)
```http
DELETE http://localhost/DWEC/Angular/ProyectoMio/backend/productos.php?id=1
```

## 🔐 Seguridad

### Verificación de Credenciales
- **Todas las contraseñas** se almacenan hasheadas con `password_hash()` (bcrypt)
- El login **consulta la base de datos** para verificar si el email existe
- La contraseña se verifica con `password_verify()` comparando el hash

### Flujo de Login:
1. Usuario envía email y password
2. PHP busca el email en la tabla `usuarios`
3. Si existe, verifica el hash de la contraseña
4. Si coincide, devuelve los datos del usuario (sin password)
5. Si no coincide, devuelve error 401

## 🧪 Probar el Backend

### Opción 1: Desde el navegador
```
http://localhost/DWEC/Angular/ProyectoMio/backend/productos.php
```

### Opción 2: Con Postman o Thunder Client
Importa las peticiones mostradas arriba.

### Opción 3: Con curl
```bash
# Login
curl -X POST http://localhost/DWEC/Angular/ProyectoMio/backend/auth.php \
  -H "Content-Type: application/json" \
  -d '{"action":"login","email":"admin@tienda.com","password":"admin123"}'

# Obtener productos
curl http://localhost/DWEC/Angular/ProyectoMio/backend/productos.php
```

## ❗ Solución de Problemas

### Error: "Error de conexión a la base de datos"
- Verifica que MySQL esté corriendo en XAMPP
- Verifica las credenciales en `config.php`
- Asegúrate de que la base de datos `tienda_online` existe

### Error: "Usuario no encontrado"
- Verifica que ejecutaste `database.sql`
- Consulta la tabla usuarios: `SELECT * FROM usuarios;`

### Error: CORS
- El archivo `config.php` ya incluye los headers CORS necesarios
- Si persiste, verifica que Angular esté en `http://localhost:4200`

## 📊 Verificar Datos en la Base de Datos

```sql
-- Ver todos los usuarios
SELECT id, nombre, email, rol, fecha_registro FROM usuarios;

-- Ver todos los productos con stock
SELECT id, nombre, precio, stock FROM productos ORDER BY stock ASC;

-- Ver productos con stock bajo
SELECT nombre, stock FROM productos WHERE stock < 20;
```
