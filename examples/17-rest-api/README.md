# REST API Generator

Generación automática de API REST desde la estructura de la base de datos con autenticación JWT.

## Setup

Antes de usar el ejemplo, crea un usuario de prueba:

```bash
php setup.php
```

Credenciales:
- Email: `admin@example.com`
- Password: `admin123`

## Características

- ✅ **Endpoints automáticos** - GET, POST, PUT, DELETE para cada tabla
- 🔐 **Autenticación JWT** - Tokens seguros con expiración
- 📚 **OpenAPI/Swagger** - Documentación automática
- 🌐 **CORS habilitado** - Listo para consumo desde frontend
- 📄 **Paginación** - Listados con paginación automática
- 🔒 **Integración RBAC** - Control de permisos opcional

## Uso Básico

```php
use Morpheus\API\RestAPIGenerator;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

$api = new RestAPIGenerator($pdo, 'your-secret-key');
$api->handleRequest();
```

## Endpoints

### Autenticación

**POST /api/v1/auth/login**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

Respuesta:
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "name": "John Doe"
  }
}
```

### CRUD Operations

**GET /api/v1/{table}** - Listar registros
```
?page=1&per_page=20
```

**GET /api/v1/{table}/{id}** - Obtener por ID

**POST /api/v1/{table}** - Crear
```json
{
  "name": "John Doe",
  "email": "john@example.com"
}
```

**PUT /api/v1/{table}/{id}** - Actualizar
```json
{
  "name": "Jane Doe"
}
```

**DELETE /api/v1/{table}/{id}** - Eliminar

### Documentación

**GET /api/v1/docs** - Especificación OpenAPI

## Autenticación

Incluir token JWT en header:
```
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

## Configuración

```php
$api = new RestAPIGenerator($pdo, 'secret-key', [
    'prefix' => '/api',
    'version' => 'v1',
    'cors' => true,
    'rate_limit' => 100
]);
```

## Integración con RBAC

```php
use Morpheus\Security\PermissionManager;

$permissionManager = new PermissionManager($pdo, 'users', $userId);
$api->setPermissionManager($permissionManager);
```

## Ejemplo con cURL

```bash
# Login
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}'

# Listar usuarios (con token)
curl -X GET http://localhost/api/v1/users \
  -H "Authorization: Bearer YOUR_TOKEN"

# Crear usuario
curl -X POST http://localhost/api/v1/users \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com"}'
```

## Respuestas

### Éxito
```json
{
  "data": [...],
  "meta": {
    "total": 100,
    "page": 1,
    "per_page": 20,
    "last_page": 5
  }
}
```

### Error
```json
{
  "error": "Record not found"
}
```

## Códigos HTTP

- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error
