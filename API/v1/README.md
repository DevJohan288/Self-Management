# API v1 (simple base)

Endpoints (base `/api/v1`):

- POST /register
  - body: {"nombre":"...","correo":"...","password":"..."}
  - crea usuario (rol por defecto: client)

- POST /login
  - body: {"correo":"...","password":"..."}
  - retorna { token, user }

- GET /profile
  - header: Authorization: Bearer <token>
  - retorna datos del usuario autenticado

- GET /users
  - header: Authorization: Bearer <token>
  - solo admin (rol == 'admin')

Notes:
- Cambia `JWT_SECRET` en `API/v1/config/database.php` por una clave segura (o cargar desde variable de entorno).
- Usa `password_hash` y `password_verify` para las contraseñas.
- Para integración con el proyecto existente, ajusta nombres de tabla/columnas si difieren.

Ejemplo curl:

curl -X POST http://localhost/Self-Management/API/v1/login -H "Content-Type: application/json" -d '{"correo":"admin@example.com","password":"secret"}'
