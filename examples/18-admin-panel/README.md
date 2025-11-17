# Admin Panel Generator

Panel de administración completo con navegación, dashboard, y gestión CRUD integrada.

## Características

- ✅ **Sidebar Navigation** - Menú lateral con iconos personalizables
- ✅ **Dashboard** - Estadísticas automáticas por tabla
- ✅ **Breadcrumbs** - Navegación contextual
- ✅ **User Menu** - Avatar y menú de usuario
- ✅ **Responsive** - Diseño adaptable a móviles
- ✅ **Integración Total** - Usa DynamicCRUD, ListGenerator, FormGenerator

## Uso Básico

```php
use Morpheus\Admin\AdminPanel;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

$admin = new AdminPanel($pdo, [
    'title' => 'Mi Admin Panel',
    'theme' => [
        'primary' => '#667eea',
        'sidebar_bg' => '#2d3748',
        'sidebar_text' => '#e2e8f0'
    ]
]);

$admin->addTable('users', ['icon' => '👥', 'label' => 'Usuarios']);
$admin->addTable('products', ['icon' => '📦', 'label' => 'Productos']);
$admin->addTable('orders', ['icon' => '🛒', 'label' => 'Pedidos']);

echo $admin->render();
```

## Configuración

### Opciones del Panel

```php
$admin = new AdminPanel($pdo, [
    'title' => 'Admin Panel',      // Título del panel
    'logo' => 'Mi Empresa',         // Logo/nombre en sidebar
    'theme' => [
        'primary' => '#667eea',     // Color primario
        'sidebar_bg' => '#2d3748',  // Fondo del sidebar
        'sidebar_text' => '#e2e8f0' // Color del texto
    ]
]);
```

### Opciones de Tabla

```php
$admin->addTable('users', [
    'label' => 'Usuarios',  // Etiqueta en el menú
    'icon' => '👥',         // Icono (emoji o HTML)
    'hidden' => false       // Ocultar del menú
]);
```

## Rutas

El panel maneja automáticamente las siguientes rutas:

- `?action=dashboard` - Dashboard principal
- `?action=list&table=users` - Listado de usuarios
- `?action=form&table=users` - Crear nuevo usuario
- `?action=form&table=users&id=1` - Editar usuario
- `?action=delete&table=users&id=1` - Eliminar usuario

## Personalización

### Tema Personalizado

```php
$admin = new AdminPanel($pdo, [
    'theme' => [
        'primary' => '#3b82f6',     // Azul
        'sidebar_bg' => '#1e293b',  // Gris oscuro
        'sidebar_text' => '#f1f5f9' // Gris claro
    ]
]);
```

### Ocultar Tablas

```php
// Tabla oculta del menú pero accesible por URL
$admin->addTable('logs', ['hidden' => true]);
```

## Integración con Autenticación

```php
use Morpheus\Security\AuthenticationManager;

session_start();

$auth = new AuthenticationManager($pdo, 'users');

if (!$auth->isAuthenticated()) {
    header('Location: login.php');
    exit;
}

$admin = new AdminPanel($pdo);
// ... configurar tablas
echo $admin->render();
```

## Integración con RBAC

```php
use Morpheus\Security\PermissionManager;

$userId = $_SESSION['user_id'];
$permissionManager = new PermissionManager($pdo, 'users', $userId);

// Solo mostrar tablas con permiso de lectura
if ($permissionManager->can('read', 'users')) {
    $admin->addTable('users', ['icon' => '👥', 'label' => 'Usuarios']);
}

if ($permissionManager->can('read', 'products')) {
    $admin->addTable('products', ['icon' => '📦', 'label' => 'Productos']);
}
```

## Características del Dashboard

El dashboard muestra automáticamente:

- **Tarjetas de estadísticas** - Conteo de registros por tabla
- **Iconos personalizados** - Según configuración de cada tabla
- **Diseño responsive** - Grid adaptable

## Navegación

### Sidebar

- Logo/título configurable
- Menú con iconos
- Indicador de página activa
- Responsive (colapsa en móvil)

### Breadcrumbs

- Navegación jerárquica
- Links a páginas anteriores
- Actualización automática

### Header

- Avatar de usuario
- Nombre de usuario
- Espacio para menú desplegable (futuro)

## Responsive Design

El panel se adapta automáticamente:

- **Desktop** (>768px): Sidebar completo (250px)
- **Mobile** (<768px): Sidebar colapsado (70px, solo iconos)

## Ejemplos de Uso

### Panel Básico

```php
$admin = new AdminPanel($pdo);
$admin->addTable('users');
$admin->addTable('posts');
echo $admin->render();
```

### Panel Completo

```php
$admin = new AdminPanel($pdo, [
    'title' => 'E-commerce Admin',
    'theme' => ['primary' => '#10b981']
]);

$admin->addTable('products', ['icon' => '📦', 'label' => 'Productos']);
$admin->addTable('orders', ['icon' => '🛒', 'label' => 'Pedidos']);
$admin->addTable('customers', ['icon' => '👥', 'label' => 'Clientes']);
$admin->addTable('categories', ['icon' => '📁', 'label' => 'Categorías']);

echo $admin->render();
```

## Próximas Características

- [ ] Menú desplegable de usuario
- [ ] Notificaciones en header
- [ ] Búsqueda global
- [ ] Modo oscuro
- [ ] Widgets personalizables en dashboard
- [ ] Gráficos y estadísticas avanzadas
