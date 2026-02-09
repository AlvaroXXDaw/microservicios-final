# Microservicios Final

Proyecto con arquitectura desacoplada: **Nginx** sirve el frontend Angular estático y enruta `/api` al **backend PHP-FPM**. La base de datos es **MySQL 8.0** y se incluye **Portainer** para gestión visual de contenedores. **ngrok** expone el servicio públicamente.

## Arquitectura

| Servicio | Imagen | Puerto |
|---|---|---|
| webserver | nginx:alpine | `${NGINX_PORT}` → 80 |
| backend | php:8.2-fpm (build) | 9000 (interno) |
| db | mysql:8.0 | 3306 (interno) |
| portainer | portainer/portainer-ce | `${PORTAINER_PORT}` → 9443 |
| ngrok | ngrok/ngrok | — |

El frontend Angular usa **rutas relativas** (`/api/...`) para comunicarse con el backend. Nginx hace reverse proxy de `/api/*.php` a PHP-FPM.

## Deploy en Ubuntu Server

### 1. Clonar repositorio

```bash
git clone https://github.com/tu-usuario/microservicios-final.git
cd microservicios-final
```

### 2. Configurar variables de entorno

```bash
cp .env.example .env
nano .env   # Rellenar credenciales y NGROK_AUTHTOKEN
```

### 3. Compilar el frontend Angular

```bash
cd frontend
npm install
npx ng build --configuration=production
cd ..
```

El build se genera en `frontend/dist/ProyectoMio/browser/`.

### 4. Levantar los contenedores

```bash
docker compose up -d --build
```

### 5. Importar la base de datos (manual)

```bash
docker compose exec -T db mysql -uroot -p$MYSQL_ROOT_PASSWORD < backend/database.sql
```

> Sustituir `$MYSQL_ROOT_PASSWORD` por el valor real si ejecutas en shell que no expande variables, o exportar previamente.

### 6. Verificar

```bash
# Frontend
curl -i http://localhost:${NGINX_PORT}/

# API
curl -i http://localhost:${NGINX_PORT}/api/productos.php

# URL pública ngrok
docker compose logs --tail=50 ngrok
```

### 7. Portainer

Accesible en `https://localhost:${PORTAINER_PORT}` (HTTPS).

## Levantar rápido (dev)

```bash
cp .env.example .env   # rellenar credenciales
make up                 # arranca todos los servicios
```

- Frontend Angular: `http://localhost:${NGINX_PORT}`
- API PHP: `http://localhost:${NGINX_PORT}/api/login.php`
- Portainer: `https://localhost:${PORTAINER_PORT}`
