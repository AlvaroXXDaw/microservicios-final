# Microservicios Final

Proyecto con arquitectura desacoplada: **Nginx** sirve el frontend Angular estático y enruta `/api` al **backend PHP-FPM**. La base de datos es **MySQL 8.0** y se incluye **Portainer** para gestión visual de contenedores.

## Levantar

```bash
cp .env.example .env   # rellenar credenciales
make up                 # arranca todos los servicios
```

- Frontend Angular: `http://localhost`
- API PHP: `http://localhost/api/login.php` (o cualquier endpoint)
- Portainer: `https://localhost:9443`
