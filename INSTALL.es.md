# Guía de Instalación - DynamicCRUD

## Requisitos

- PHP 8.0 o superior
- MySQL 5.7+ o MariaDB 10.2+
- Extensión PDO MySQL habilitada

## Instalación

### 1. Clonar o descargar el proyecto

```bash
git clone https://github.com/tu-usuario/dynamiccrud.git
cd dynamiccrud
```

### 2. Configurar base de datos

Ejecuta el script SQL de ejemplo:

```bash
mysql -u root -p < examples/setup.sql
```

O manualmente:

```sql
CREATE DATABASE test;
USE test;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL COMMENT '{"label": "Nombre completo"}',
    email VARCHAR(255) NOT NULL COMMENT '{"type": "email", "label": "Correo electrónico"}',
    website VARCHAR(255) COMMENT '{"type": "url", "label": "Sitio web"}',
    age INT COMMENT '{"label": "Edad"}',
    bio TEXT COMMENT '{"label": "Biografía"}',
    birth_date DATE COMMENT '{"label": "Fecha de nacimiento"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. Configurar conexión

Edita `examples/index.php` con tus credenciales:

```php
$pdo = new PDO('mysql:host=localhost;dbname=test', 'tu_usuario', 'tu_password');
```

### 4. Probar el ejemplo

Inicia un servidor PHP:

```bash
php -S localhost:8000 -t examples
```

Abre en tu navegador: http://localhost:8000

## Uso en tu proyecto

```php
<?php
require_once 'vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=tu_bd', 'usuario', 'password');
$crud = new DynamicCRUD($pdo, 'tu_tabla');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    if ($result['success']) {
        echo "Guardado con ID: {$result['id']}";
    }
} else {
    echo $crud->renderForm();
}
```

## Ejecutar Tests

```bash
php tests/SchemaAnalyzerTest.php
```

## Próximos Pasos

- Personaliza los estilos CSS del formulario
- Añade metadatos personalizados en tus tablas
- Revisa la documentación completa en README.md
