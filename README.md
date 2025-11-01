# DynamicCRUD

Librería PHP para generar formularios CRUD dinámicos con validación automática basada en la estructura de base de datos.

## Instalación

```bash
composer require dynamiccrud/dynamiccrud
```

## Uso Básico

```php
<?php
require 'vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
$crud = new DynamicCRUD($pdo, 'users');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    if ($result['success']) {
        echo "Guardado con ID: {$result['id']}";
    }
} else {
    echo $crud->renderForm($_GET['id'] ?? null);
}
```

## Configuración de Metadatos

Define metadatos personalizados en comentarios de columnas:

```sql
ALTER TABLE users 
MODIFY COLUMN email VARCHAR(255) COMMENT '{"type": "email", "label": "Tu correo"}';
```

## Características

- ✅ Generación automática de formularios desde estructura SQL
- ✅ Validación servidor con tipos SQL y metadatos
- ✅ Protección CSRF integrada
- ✅ Sanitización automática de datos
- ✅ Sentencias preparadas (PDO)

## Requisitos

- PHP 8.0+
- PDO MySQL

## Licencia

MIT
