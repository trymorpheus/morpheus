# DynamicCRUD

[![Tests](https://github.com/mcarbonell/DynamicCRUD/workflows/Tests/badge.svg)](https://github.com/mcarbonell/DynamicCRUD/actions)
[![Code Quality](https://github.com/mcarbonell/DynamicCRUD/workflows/Code%20Quality/badge.svg)](https://github.com/mcarbonell/DynamicCRUD/actions)
[![Packagist Version](https://img.shields.io/packagist/v/dynamiccrud/dynamiccrud)](https://packagist.org/packages/dynamiccrud/dynamiccrud)
[![PHP Version](https://img.shields.io/packagist/php-v/dynamiccrud/dynamiccrud)](https://packagist.org/packages/dynamiccrud/dynamiccrud)
[![License](https://img.shields.io/github/license/mcarbonell/DynamicCRUD)](https://github.com/mcarbonell/DynamicCRUD/blob/main/LICENSE)
[![Downloads](https://img.shields.io/packagist/dt/dynamiccrud/dynamiccrud)](https://packagist.org/packages/dynamiccrud/dynamiccrud)

**Una poderosa librería PHP que genera automáticamente formularios CRUD con validación basándose en la estructura de tu base de datos.**

Deja de escribir código CRUD repetitivo. DynamicCRUD analiza tu esquema MySQL y crea formularios completamente funcionales con validación, seguridad y características avanzadas listas para usar.

[🇬🇧 English Documentation](README.md)

---

## ✨ Características

### 🚀 Núcleo
- **Generación de formularios sin configuración** desde esquema SQL
- **Validación automática** (servidor + cliente JavaScript)
- **Protección CSRF** integrada
- **Prevención de inyección SQL** con sentencias preparadas
- **Manejo inteligente de NULL** para campos opcionales
- **Subida de archivos** con validación MIME

### 🔗 Relaciones
- **Auto-detección de claves foráneas** con selects desplegables
- **Relaciones muchos-a-muchos** con multi-select
- **Columnas de visualización personalizadas** para datos relacionados

### ⚡ Avanzado
- **Herramienta CLI** - Interfaz de línea de comandos para gestión de proyectos
- **Autenticación** - Registro, login, logout con limitación de intentos
- **RBAC** - Control de acceso basado en roles con seguridad a nivel de fila
- **Eliminación Suave** - Marcar registros como eliminados, restaurar o eliminar permanentemente
- **Soporte multi-base de datos** (MySQL, PostgreSQL)
- **Internacionalización (i18n)** - 3 idiomas incluidos (EN, ES, FR)
- **Sistema de Plantillas** - Sintaxis tipo Blade para layouts personalizados
- **Sistema de Hooks/Eventos** (10 hooks de ciclo de vida)
- **Campos virtuales** (confirmación de contraseña, aceptación de términos)
- **Transacciones automáticas** con rollback en error
- **Registro de auditoría** para seguimiento de cambios
- **Sistema de caché** para metadatos de esquema
- **Soporte de campos ENUM** con selects auto-generados
- **Accesibilidad** (etiquetas ARIA, navegación por teclado)

---

## 📦 Instalación

```bash
composer require dynamiccrud/dynamiccrud
```

**Requisitos:** PHP 8.0+, MySQL 5.7+ o PostgreSQL 12+, extensión PDO

### Herramienta CLI

Después de la instalación, inicializa tu proyecto:

```bash
php bin/dynamiccrud init
php bin/dynamiccrud list:tables
php bin/dynamiccrud generate:metadata users
```

---

## ✨ Novedades en v3.3

**Librería de Componentes UI** - ¡15 componentes reutilizables, accesibles y hermosos para construir UIs modernas!

```php
use DynamicCRUD\\UI\\Components;

// Establecer tema personalizado
Components::setTheme(['primary' => '#667eea']);

// Usar componentes
echo Components::alert('¡Éxito!', 'success');
echo Components::badge('Nuevo', 'primary');
echo Components::button('Haz clic', 'primary');
echo Components::card('Título', '<p>Contenido</p>');
echo Components::modal('id', 'Título', 'Contenido');
echo Components::tabs([...]);
echo Components::table(['Nombre', 'Email'], [[...]]);
echo Components::pagination(3, 10);
```

**Características:**
- 🎨 **15 Componentes** - Alert, Badge, Button, Card, Modal, Tabs, Accordion, Table, y más
- 🎭 **Tematizable** - Personaliza colores para tu marca
- ♿ **Accesible** - Etiquetas ARIA y navegación por teclado
- 📱 **Responsive** - Diseño mobile-first
- 🚀 **Sin Dependencias** - PHP puro, sin librerías externas
- 💅 **Diseño Moderno** - Estilo limpio y profesional

👉 [Ver Ejemplo de Componentes UI](examples/20-ui-components/)

---

## ✨ Novedades en v3.2

**Motor de Flujo de Trabajo** - ¡Gestión de estados con transiciones, permisos y seguimiento de historial!

```php
$crud = new DynamicCRUD($pdo, 'orders');

$crud->enableWorkflow([
    'field' => 'status',
    'states' => ['pending', 'processing', 'shipped', 'delivered'],
    'transitions' => [
        'process' => [
            'from' => 'pending',
            'to' => 'processing',
            'label' => 'Procesar Pedido',
            'permissions' => ['admin', 'manager']
        ],
        'ship' => [
            'from' => 'processing',
            'to' => 'shipped',
            'permissions' => ['admin', 'warehouse']
        ]
    ],
    'history' => true
]);

echo $crud->renderForm($id); // ¡Botones de transición automáticos!
```

👉 [Ver Ejemplo de Workflow](examples/19-workflow/)

---

## ✨ Novedades en v3.1

**Generador de Panel de Administración** - ¡Panel de administración completo con navegación, dashboard y CRUD integrado!

```php
use DynamicCRUD\\Admin\\AdminPanel;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

$admin = new AdminPanel($pdo, [
    'title' => 'Mi Panel Admin',
    'theme' => [
        'primary' => '#667eea',
        'sidebar_bg' => '#2d3748',
        'sidebar_text' => '#e2e8f0'
    ]
]);

$admin->addTable('users', ['icon' => '👥', 'label' => 'Usuarios']);
$admin->addTable('products', ['icon' => '📦', 'label' => 'Productos']);
$admin->addTable('orders', ['icon' => '🛒', 'label' => 'Pedidos']);

echo $admin->render(); // ¡Panel admin completo!
```

👉 [Ver Ejemplo de Panel Admin](examples/18-admin-panel/)

---

## 🎯 Inicio Rápido

### 1. CRUD Básico (¡3 líneas de código!)

```php
<?php
require 'vendor/autoload.php';

use DynamicCRUD\\DynamicCRUD;

// MySQL
$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');
// PostgreSQL
// $pdo = new PDO('pgsql:host=localhost;dbname=mydb', 'user', 'pass');

$crud = new DynamicCRUD($pdo, 'users');

// ¡Eso es todo! Maneja tanto visualización como envío
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    echo $result['success'] ? \"¡Guardado! ID: {$result['id']}\" : \"Error\";
} else {
    echo $crud->renderForm($_GET['id'] ?? null); // null = crear, ID = editar
}
```

### 2. Personalizar con Metadatos JSON

Añade metadatos a las columnas de tu tabla usando JSON en comentarios:

```sql
ALTER TABLE users 
MODIFY COLUMN email VARCHAR(255) 
COMMENT '{\"type\": \"email\", \"label\": \"Correo Electrónico\", \"tooltip\": \"Nunca compartiremos tu email\"}';

ALTER TABLE users 
MODIFY COLUMN age INT 
COMMENT '{\"type\": \"number\", \"min\": 18, \"max\": 120}';

ALTER TABLE users 
MODIFY COLUMN created_at TIMESTAMP 
COMMENT '{\"hidden\": true}';
```

### 3. Claves Foráneas (¡Automático!)

```php
// Si la tabla 'posts' tiene una clave foránea a 'users', 
// DynamicCRUD crea automáticamente un desplegable con nombres de usuario
$crud = new DynamicCRUD($pdo, 'posts');
echo $crud->renderForm();
// El desplegable muestra: \"John Doe\", \"Jane Smith\", etc.
```

---

## 📚 Documentación

### Características v3.3 (¡NUEVO!)
- [Guía de Componentes UI](docs/UI_COMPONENTS.md) - 15 componentes reutilizables
- Librería de Componentes - Alerts, badges, buttons, cards, modals, tabs, tables, y más

### Características v3.2
- [Guía del Motor de Workflow](docs/WORKFLOW.md) - Gestión de estados con transiciones
- Sistema de Workflow - Transiciones basadas en permisos y seguimiento de historial

### Características v3.1
- Generador de Panel Admin - Panel admin completo con dashboard
- Navegación Lateral - Menú personalizable con iconos

### Características v3.0
- Generador de API REST - API REST automática con autenticación JWT
- OpenAPI/Swagger - Documentación de API auto-generada

### Características v2.9
- Subida Múltiple de Archivos - Drag & drop con previsualizaciones
- Integración de Temas - Tematización white-label desde Config Global

### Características v2.8
- [Guía de Metadatos Globales](docs/GLOBAL_METADATA.md) - Configuración centralizada

### Características Principales
- [Guía del Sistema de Plantillas](docs/TEMPLATES.md) - Plantillas tipo Blade
- [Guía de Internacionalización (i18n)](docs/I18N.md) - Soporte multi-idioma
- [Guía del Sistema de Hooks](docs/HOOKS.md) - 10 hooks de ciclo de vida explicados
- [Guía de Campos Virtuales](docs/VIRTUAL_FIELDS.md) - Confirmación de contraseña, aceptación de términos
- [Relaciones Muchos-a-Muchos](docs/MANY_TO_MANY.md) - Guía de configuración M:N
- [Guía de Personalización](docs/CUSTOMIZATION.md) - Opciones de metadatos

### Primeros Pasos
- [Guía de Inicio Rápido](docs/QUICKSTART.md) - Comienza en 5 minutos
- [Guía de Migración](docs/MIGRATION.md) - Actualiza entre versiones
- [Mejores Prácticas](docs/BEST_PRACTICES.md) - Patrones listos para producción

### Configuración y Contribución
- [Configuración Docker](DOCKER_SETUP.md) - MySQL y PostgreSQL con Docker
- [Registro de Cambios](CHANGELOG.md) - Historial de versiones
- [Contribuir](CONTRIBUTING.md) - Cómo contribuir

---

## 🧪 Pruebas

DynamicCRUD tiene cobertura de pruebas completa:

- **367 pruebas** con **745+ aserciones**
- **100% de tasa de éxito** (361 pasando, 6 omitidas)
- **90% de cobertura de código**
- CI/CD automatizado con GitHub Actions
- Pruebas ejecutadas en PHP 8.0, 8.1, 8.2, 8.3

```bash
# Ejecutar todas las pruebas
php vendor/phpunit/phpunit/phpunit

# Ejecutar suite de pruebas específica
php vendor/phpunit/phpunit/phpunit tests/AuthenticationManagerTest.php
php vendor/phpunit/phpunit/phpunit tests/PermissionManagerTest.php
php vendor/phpunit/phpunit/phpunit tests/ComponentsTest.php
```

---

## 📊 Estadísticas del Proyecto

- **39 clases PHP** (~14,000 líneas)
- **38 ejemplos funcionales** (1 en v3.3, 1 en v3.2, 1 en v3.1, 1 en v3.0, 2 en v2.9, 1 en v2.8, 1 en v2.7, 2 en v2.5, 2 en v2.3, 4 en v2.2, 6 en v2.1, 4 en v2.0)
- **22 documentos técnicos**
- **367 pruebas automatizadas** (100% pasando, 90% cobertura)
- **19 comandos CLI**
- **Idiomas soportados**: 3 (Inglés, Español, Francés)
- **Bases de datos soportadas**: 2 (MySQL, PostgreSQL)
- **Motor de plantillas**: Sintaxis tipo Blade
- **Autenticación**: Registro, login, logout, reset de contraseña, limitación de intentos
- **RBAC**: Permisos a nivel de tabla + fila
- **Eliminación Suave**: Eliminar, restaurar, forzar eliminación
- **Subida de Archivos**: Simple + múltiple con drag & drop
- **Tematización**: Config global con variables CSS
- **API REST**: Generación automática con autenticación JWT
- **Panel Admin**: Interfaz admin completa con dashboard
- **Workflow**: Gestión de estados con transiciones
- **Componentes UI**: 15 componentes reutilizables

---

## 🤝 Contribuir

¡Las contribuciones son bienvenidas! Por favor lee [CONTRIBUTING.md](CONTRIBUTING.md) para las pautas.

1. Haz fork del repositorio
2. Crea una rama de característica: `git checkout -b feature/amazing-feature`
3. Haz commit de tus cambios: `git commit -m 'Add amazing feature'`
4. Push a la rama: `git push origin feature/amazing-feature`
5. Abre un Pull Request

---

## 👥 Créditos

**Creador y Líder del Proyecto**: [Mario Raúl Carbonell Martínez](https://github.com/mcarbonell)  
**Desarrollo**: Amazon Q, Gemini 2.5 Pro

---

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

---

## 🌟 Muestra tu Apoyo

Si encuentras útil este proyecto, por favor considera:
- ⭐ Dar estrella al repositorio
- 🐛 Reportar bugs
- 💡 Sugerir nuevas características
- 📢 Compartir con otros

---

**Hecho con ❤️ por Mario Raúl Carbonell Martínez**
