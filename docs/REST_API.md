# REST API Generator

Complete guide to DynamicCRUD's automatic REST API generation with JWT authentication.

## Table of Contents

- [Overview](#overview)
- [Quick Start](#quick-start)
- [Authentication](#authentication)
- [Endpoints](#endpoints)
- [Configuration](#configuration)
- [RBAC Integration](#rbac-integration)
- [OpenAPI Documentation](#openapi-documentation)
- [Error Handling](#error-handling)
- [Examples](#examples)

## Overview

The REST API Generator automatically creates RESTful endpoints for all database tables with:

- **Zero configuration** - Works out of the box
- **JWT authentication** - Secure token-based auth
- **CRUD operations** - GET, POST, PUT, DELETE for every table
- **Pagination** - Automatic list pagination
- **OpenAPI docs** - Auto-generated Swagger documentation
- **CORS support** - Ready for frontend consumption
- **RBAC integration** - Optional permission control

## Quick Start

### Basic Setup

```php
<?php
require 'vendor/autoload.php';

use Morpheus\API\RestAPIGenerator;

$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');

$api = new RestAPIGenerator($pdo, 'your-secret-key-here');
$api->handleRequest();
```

That's it! Your API is now live at `/api/v1/`.

### Configuration Options

```php
$api = new RestAPIGenerator($pdo, 'secret-key', [
    'prefix' => '/api',           // URL prefix
    'version' => 'v1',            // API version
    'cors' => true,               // Enable CORS
    'rate_limit' => 100           // Requests per minute
]);
```

## Authentication

### Login

**Endpoint:** `POST /api/v1/auth/login`

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "name": "John Doe",
    "role": "admin"
  }
}
```

### Using the Token

Include the JWT token in the `Authorization` header:

```bash
curl -X GET http://localhost/api/v1/users \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Token Expiration

Tokens expire after 24 hours. The expiration time is included in the JWT payload:

```json
{
  "user_id": 1,
  "email": "user@example.com",
  "exp": 1735689600
}
```

## Endpoints

### List Records

**Endpoint:** `GET /api/v1/{table}`

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Records per page (default: 20, max: 100)

**Example:**
```bash
curl -X GET "http://localhost/api/v1/users?page=1&per_page=20" \
  -H "Authorization: Bearer TOKEN"
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    {
      "id": 2,
      "name": "Jane Smith",
      "email": "jane@example.com"
    }
  ],
  "meta": {
    "total": 50,
    "page": 1,
    "per_page": 20,
    "last_page": 3
  }
}
```

### Get Single Record

**Endpoint:** `GET /api/v1/{table}/{id}`

**Example:**
```bash
curl -X GET http://localhost/api/v1/users/1 \
  -H "Authorization: Bearer TOKEN"
```

**Response:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "created_at": "2024-01-01 12:00:00"
}
```

### Create Record

**Endpoint:** `POST /api/v1/{table}`

**Example:**
```bash
curl -X POST http://localhost/api/v1/users \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New User",
    "email": "newuser@example.com",
    "password": "password123"
  }'
```

**Response:**
```json
{
  "id": 3,
  "message": "Created successfully"
}
```

### Update Record

**Endpoint:** `PUT /api/v1/{table}/{id}`

**Example:**
```bash
curl -X PUT http://localhost/api/v1/users/3 \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Name"
  }'
```

**Response:**
```json
{
  "message": "Updated successfully"
}
```

### Delete Record

**Endpoint:** `DELETE /api/v1/{table}/{id}`

**Example:**
```bash
curl -X DELETE http://localhost/api/v1/users/3 \
  -H "Authorization: Bearer TOKEN"
```

**Response:**
```json
{
  "message": "Deleted successfully"
}
```

## Configuration

### Custom Prefix and Version

```php
$api = new RestAPIGenerator($pdo, 'secret', [
    'prefix' => '/myapi',
    'version' => 'v2'
]);

// Endpoints now at: /myapi/v2/users
```

### Disable CORS

```php
$api = new RestAPIGenerator($pdo, 'secret', [
    'cors' => false
]);
```

### Rate Limiting

```php
$api = new RestAPIGenerator($pdo, 'secret', [
    'rate_limit' => 60  // 60 requests per minute
]);
```

## RBAC Integration

Integrate with DynamicCRUD's permission system:

```php
use Morpheus\API\RestAPIGenerator;
use Morpheus\Security\PermissionManager;

$api = new RestAPIGenerator($pdo, 'secret');

// Get user from JWT token
$user = $api->authenticate();

if ($user) {
    $permissionManager = new PermissionManager(
        $pdo, 
        'users', 
        $user['user_id']
    );
    
    $api->setPermissionManager($permissionManager);
}

$api->handleRequest();
```

Now all endpoints respect table and row-level permissions.

## OpenAPI Documentation

### Get Specification

**Endpoint:** `GET /api/v1/docs`

Returns complete OpenAPI 3.0 specification:

```json
{
  "openapi": "3.0.0",
  "info": {
    "title": "DynamicCRUD REST API",
    "version": "v1"
  },
  "paths": {
    "/users": {
      "get": {
        "summary": "List users",
        "parameters": [...]
      },
      "post": {
        "summary": "Create users"
      }
    },
    "/users/{id}": {
      "get": {...},
      "put": {...},
      "delete": {...}
    }
  }
}
```

### Import to Swagger UI

1. Get the spec: `http://localhost/api/v1/docs`
2. Open [Swagger Editor](https://editor.swagger.io/)
3. Import the JSON
4. Test your API interactively

## Error Handling

### HTTP Status Codes

- `200` - OK
- `201` - Created
- `400` - Bad Request (validation error)
- `401` - Unauthorized (invalid/missing token)
- `403` - Forbidden (permission denied)
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error

### Error Response Format

```json
{
  "error": "Error message here"
}
```

### Common Errors

**Invalid Credentials:**
```json
{
  "error": "Invalid credentials"
}
```

**Permission Denied:**
```json
{
  "error": "Permission denied"
}
```

**Record Not Found:**
```json
{
  "error": "Record not found"
}
```

**Validation Error:**
```json
{
  "error": "Validation failed",
  "errors": {
    "email": "Email is required",
    "name": "Name must be at least 3 characters"
  }
}
```

## Examples

### JavaScript/Fetch

```javascript
// Login
const loginResponse = await fetch('http://localhost/api/v1/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'admin@example.com',
    password: 'admin123'
  })
});

const { token } = await loginResponse.json();

// Get users
const usersResponse = await fetch('http://localhost/api/v1/users', {
  headers: { 'Authorization': `Bearer ${token}` }
});

const { data, meta } = await usersResponse.json();
console.log(`Found ${meta.total} users`);
```

### Python/Requests

```python
import requests

# Login
response = requests.post('http://localhost/api/v1/auth/login', json={
    'email': 'admin@example.com',
    'password': 'admin123'
})

token = response.json()['token']

# Get users
headers = {'Authorization': f'Bearer {token}'}
response = requests.get('http://localhost/api/v1/users', headers=headers)

data = response.json()
print(f"Found {data['meta']['total']} users")
```

### PHP/Guzzle

```php
use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'http://localhost/api/v1/']);

// Login
$response = $client->post('auth/login', [
    'json' => [
        'email' => 'admin@example.com',
        'password' => 'admin123'
    ]
]);

$token = json_decode($response->getBody())->token;

// Get users
$response = $client->get('users', [
    'headers' => ['Authorization' => "Bearer $token"]
]);

$data = json_decode($response->getBody());
echo "Found {$data->meta->total} users\n";
```

### cURL

```bash
#!/bin/bash

# Login and get token
TOKEN=$(curl -s -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}' \
  | jq -r '.token')

# Get users
curl -X GET http://localhost/api/v1/users \
  -H "Authorization: Bearer $TOKEN" \
  | jq .

# Create user
curl -X POST http://localhost/api/v1/users \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "New User",
    "email": "newuser@example.com",
    "password": "password123"
  }' \
  | jq .
```

## Security Best Practices

### 1. Use Strong Secret Keys

```php
// Generate a secure random key
$secretKey = bin2hex(random_bytes(32));

$api = new RestAPIGenerator($pdo, $secretKey);
```

### 2. Use HTTPS in Production

```php
// Force HTTPS
if ($_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
```

### 3. Implement Rate Limiting

```php
$api = new RestAPIGenerator($pdo, 'secret', [
    'rate_limit' => 60  // 60 requests per minute
]);
```

### 4. Validate Input

The API automatically uses DynamicCRUD's validation engine, but you can add custom validation:

```php
$crud = new Morpheus($pdo, 'users');
$crud->addHook('beforeValidate', function($data) {
    // Custom validation logic
    return $data;
});
```

### 5. Use RBAC

Always integrate with PermissionManager for production APIs:

```php
$api->setPermissionManager($permissionManager);
```

## Troubleshooting

### CORS Issues

If you get CORS errors, ensure CORS is enabled:

```php
$api = new RestAPIGenerator($pdo, 'secret', [
    'cors' => true
]);
```

### Token Not Working

Check that:
1. Token is included in `Authorization` header
2. Token format is `Bearer YOUR_TOKEN`
3. Token hasn't expired (24 hours)
4. Secret key matches between generation and verification

### 404 Errors

Ensure your web server is configured to route all requests to the API file:

**Apache (.htaccess):**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^api/(.*)$ api.php [L,QSA]
```

**Nginx:**
```nginx
location /api/ {
    try_files $uri $uri/ /api.php?$query_string;
}
```

## Next Steps

- [View REST API Example](../examples/17-rest-api/)
- [RBAC Guide](RBAC.md)
- [Security Best Practices](BEST_PRACTICES.md)
- [Main Documentation](../README.md)
