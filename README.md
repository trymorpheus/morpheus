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

### Fase 1 (MVP)
- ✅ Generación automática de formularios desde estructura SQL
- ✅ Validación servidor con tipos SQL y metadatos
- ✅ Protección CSRF integrada
- ✅ Sanitización automática de datos
- ✅ Sentencias preparadas (PDO)
- ✅ Operaciones CREATE y UPDATE

### Fase 2 (Características Intermedias)
- ✅ Detección automática de claves foráneas
- ✅ Selects con datos de tablas relacionadas
- ✅ Sistema de caché (FileCacheStrategy)
- ✅ Operaciones READ (paginación) y DELETE
- ✅ Manejo correcto de valores NULL
- ✅ Metadatos: hidden, display_column

### Fase 3 (Validación Cliente y Archivos)
- ✅ Validación JavaScript en tiempo real
- ✅ Subida de archivos con validación MIME y tamaño
- ✅ Preview de imágenes
- ✅ Tooltips informativos
- ✅ Mejoras de accesibilidad (ARIA, navegación por teclado)
- ✅ Mensajes mejorados con animaciones
- ✅ Indicadores de carga

### Fase 4 (Características Avanzadas)
- ✅ Sistema de Hooks/Eventos (10 hooks disponibles)
- ✅ Transacciones automáticas con rollback
- ✅ Soporte para campos ENUM
- ✅ Relaciones muchos-a-muchos con select multiple
- ✅ Sistema de auditoría opcional

### Fase 5 (Futuro)
- [ ] Soporte PostgreSQL
- [ ] UI avanzada para M:N (checkboxes, búsqueda)
- [ ] Campos virtuales
- [ ] Internacionalización

## Requisitos

- PHP 8.0+
- PDO MySQL

## Autoría

**Creador y Director del Proyecto**: Mario Raúl Carbonell Martínez  
**Desarrollo**: Amazon Q, Gemini 2.5 Pro

## Licencia

MIT
