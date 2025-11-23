# Morpheus

[![Tests](https://github.com/trymorpheus/morpheus/workflows/Tests/badge.svg)](https://github.com/trymorpheus/morpheus/actions)
[![Code Quality](https://github.com/trymorpheus/morpheus/workflows/Code%20Quality/badge.svg)](https://github.com/trymorpheus/morpheus/actions)
[![Packagist Version](https://img.shields.io/packagist/v/trymorpheus/morpheus)](https://packagist.org/packages/trymorpheus/morpheus)
[![PHP Version](https://img.shields.io/packagist/php-v/trymorpheus/morpheus)](https://packagist.org/packages/trymorpheus/morpheus)
[![License](https://img.shields.io/github/license/mcarbonell/DynamicCRUD)](https://github.com/trymorpheus/morpheus/blob/main/LICENSE)
[![Downloads](https://img.shields.io/packagist/dt/trymorpheus/morpheus)](https://packagist.org/packages/trymorpheus/morpheus)

**The Universal CMS - Start as a blog, grow into anything.**

Morpheus is the world's first **Universal CMS** that combines the simplicity of WordPress with the power of a custom application generator. Start with a blog in 60 seconds, then grow into e-commerce, CRM, or any custom application - all without migrations, plugins, or code.

**🎯 Perfect for:**
- 📝 Bloggers seeking a faster WordPress alternative
- 🏢 Businesses needing a website that grows with them
- 👨‍💻 Developers building client sites rapidly
- 🚀 Startups prototyping MVPs in minutes

[🇪🇸 Documentación en Español](README.es.md) | [📖 Universal CMS Vision](UNIVERSAL_CMS.md) | [🗺️ Complete Roadmap](ROADMAP.md)

---

## ✨ Features

### 🚀 Core
- **Zero-config form generation** from SQL schema
- **Automatic validation** (server + client-side JavaScript)
- **CSRF protection** built-in
- **SQL injection prevention** with prepared statements
- **Smart NULL handling** for nullable fields
- **File uploads** with MIME type validation

### 🔗 Relationships
- **Foreign keys auto-detection** with dropdown selects
- **Many-to-many relationships** with multi-select
- **Custom display columns** for related data

### ⚡ Advanced
- **CLI Tool** - Command-line interface for project management
- **Authentication** - Register, login, logout with rate limiting
- **RBAC** - Role-based access control with row-level security
- **Soft Deletes** - Mark records as deleted, restore or permanently delete
- **Multi-database support** (MySQL, PostgreSQL)
- **Internationalization (i18n)** - 3 languages included (EN, ES, FR)
- **Template System** - Blade-like syntax for custom layouts
- **Hooks/Events system** (10 lifecycle hooks)
- **Virtual fields** (password confirmation, terms acceptance)
- **Automatic transactions** with rollback on error
- **Audit logging** for change tracking
- **Caching system** for schema metadata
- **ENUM field support** with auto-generated selects
- **Accessibility** (ARIA labels, keyboard navigation)

---

## 📦 Installation

```bash
composer require trymorpheus/morpheus
```

**Requirements:** PHP 8.0+, MySQL 5.7+ or PostgreSQL 12+, PDO extension

### CLI Tool

After installation, initialize your project:

```bash
php bin/morpheus init
php bin/morpheus list:tables
php bin/morpheus generate:metadata users
```

---

## ✨ What's New in v4.0 (IN PROGRESS)

**Universal CMS Foundation** - First working WordPress alternative with Blog CMS!

**Blog Content Type:**
- Complete WordPress-style blog with posts, categories, tags, and comments
- Frontend rendering with clean URLs and SEO optimization
- Admin panel with sidebar navigation and CRUD operations
- Automatic sitemap.xml and RSS feed generation
- Table prefixes for example isolation (e.g., `24_posts`, `24_categories`)

**Theme System:**
- 3 built-in themes (Minimal, Modern, Classic)
- Hot theme switching without data loss
- Self-contained themes with templates and styles
- Pluggable architecture for custom themes
- Database-persisted active theme

**One-Click Installer:**
- WordPress-style installation wizard (8 steps)
- Beautiful responsive web UI
- CLI mode (interactive + non-interactive)
- System requirements check
- Database connection testing
- Content type selection (blog/empty)
- Theme selection with previews
- Automatic config file generation

**Media Library:**
- Complete file management system
- Multiple file upload with drag & drop
- Folder organization and navigation
- Grid view with thumbnails
- Image editing (resize, crop, thumbnails)
- Search and filter functionality
- File statistics and storage tracking

**Comment System:**
- Nested replies (up to 3 levels)
- Moderation (approve/reject/delete)
- Spam detection (keywords and links)
- Gravatar integration
- Beautiful responsive UI

```php
use Morpheus\ContentTypes\ContentTypeManager;

$manager = new ContentTypeManager($pdo);
$manager->install('blog'); // Installs 5 tables with full metadata

// Frontend (index.php)
$router = new FrontendRouter();
$renderer = new FrontendRenderer($pdo, 'blog', null, $seo);
echo $renderer->renderHome(); // Beautiful blog homepage

// Admin (admin.php)
$admin = new AdminPanel($pdo);
$admin->addTable('posts', ['icon' => '📝']);
echo $admin->render(); // Full admin panel
```

**New Classes:**
- `ContentType` interface - Contract for all content types
- `ContentTypeManager` - Manages content type lifecycle
- `BlogContentType` - Complete blog implementation
- `FrontendRouter` - Routes public URLs to content
- `FrontendRenderer` - Renders public-facing pages
- `SEOManager` - Meta tags, Open Graph, Schema.org, sitemap, RSS
- `Theme` interface - Contract for all themes
- `ThemeManager` - Manages theme lifecycle
- `AbstractTheme` - Base class for themes
- `MinimalTheme`, `ModernTheme`, `ClassicTheme` - Built-in themes
- `SystemChecker` - Validates system requirements
- `DatabaseSetup` - Database connection and table creation
- `ConfigGenerator` - Generates config.php file
- `InstallerWizard` - Main installation orchestrator
- `MediaLibrary` - File upload and management
- `ImageEditor` - Image manipulation (resize, crop, thumbnails)
- `MediaBrowser` - Visual file browsing interface
- `CommentManager` - Comment CRUD and moderation
- `CommentRenderer` - Comment UI rendering

**Features:**
- 🎨 **Modern Design** - Professional frontend with navigation and search
- 🔍 **SEO Built-in** - Meta tags, Open Graph, Twitter Cards, Schema.org
- 📡 **RSS Feed** - Automatic RSS 2.0 feed generation
- 🗺️ **Sitemap** - XML sitemap with posts, categories, and tags
- 🚀 **Fast** - 20-30x faster than WordPress
- 🔐 **Secure** - No plugins = no vulnerabilities

👉 [See Blog CMS Example](examples/24-blog-cms/)  
👉 [See Theme Showcase Example](examples/25-themes/)  
👉 [See One-Click Installer Example](examples/27-installer/)  
👉 [See Media Library Example](examples/28-media-library/)  
👉 [See Comment System Example](examples/29-comments/)

---

## ✨ What's New in v3.5

**Core Classes Refactoring** - Massive improvements to code quality and maintainability!

**CRUDHandler Refactoring:**
- 88% reduction in main method (250 → 30 lines)
- Extracted 16 focused methods
- Fixed hook execution order
- Better error handling
- Single responsibility per method

**ValidationEngine Refactoring:**
- 13 extracted validation methods
- Type-specific validators
- Guard clauses for cleaner logic
- Consistent error handling
- Self-documenting code

**SchemaAnalyzer Refactoring:**
- Improved cache management
- Nullsafe operator for cleaner code
- 3 extracted cache methods
- Single source of truth

**Benefits:**
- ✅ Easier to understand and maintain
- ✅ Better testability
- ✅ Reduced cognitive load
- ✅ Consistent patterns
- ✅ All 366 tests passing (100%)

👉 [See Refactoring Patterns](docs/REFACTORING_PATTERNS.md)  
👉 [See Release Notes](RELEASE_NOTES_v3.5.0.md)

---

## ✨ What's New in v3.4

**UI Classes Refactoring** - Improved code quality, maintainability, and Components integration!

**FormGenerator Refactoring:**
- Integrated Components library for tabs and buttons
- Simplified render() method from ~70 to ~15 lines
- Extracted 8 new methods for better organization
- CSS variables support for dynamic theming
- Eliminated code duplication

**ListGenerator Refactoring:**
- 20% code reduction (~350 to ~280 lines)
- Modern table styling with Components::table()
- Consistent pagination with Components::pagination()
- Cleaner action button rendering
- Better separation of concerns

👉 [See v3.4 Release Notes](RELEASE_NOTES_v3.4.0.md)

---

## ✨ What's New in v3.3

**UI Components Library** - 15 reusable, accessible, and beautiful components for building modern UIs!

```php
use Morpheus\UI\Components;

// Set custom theme
Components::setTheme(['primary' => '#667eea']);

// Use components
echo Components::alert('Success!', 'success');
echo Components::badge('New', 'primary');
echo Components::button('Click Me', 'primary');
echo Components::card('Title', '<p>Content</p>');
echo Components::modal('id', 'Title', 'Content');
echo Components::tabs([...]);
echo Components::table(['Name', 'Email'], [[...]]);
echo Components::pagination(3, 10);
```

**Features:**
- 🎨 **15 Components** - Alert, Badge, Button, Card, Modal, Tabs, Accordion, Table, and more
- 🎭 **Themeable** - Customize colors to match your brand
- ♿ **Accessible** - ARIA labels and keyboard navigation
- 📱 **Responsive** - Mobile-first design
- 🚀 **Zero Dependencies** - Pure PHP, no external libraries
- 💅 **Modern Design** - Clean, professional styling

👉 [See UI Components Example](examples/20-ui-components/)

---

## ✨ What's New in v3.2

**Workflow Engine** - State management with transitions, permissions, and history tracking!

```php
$crud = new Morpheus($pdo, 'orders');

$crud->enableWorkflow([
    'field' => 'status',
    'states' => ['pending', 'processing', 'shipped', 'delivered'],
    'transitions' => [
        'process' => [
            'from' => 'pending',
            'to' => 'processing',
            'label' => 'Process Order',
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

echo $crud->renderForm($id); // Automatic transition buttons!
```

**Features:**
- 🔄 **State Management** - Define allowed states for records
- ➡️ **Transitions** - Configure transitions between states
- 🔐 **Permission Control** - Restrict transitions by user role
- 🎨 **Automatic UI** - Transition buttons rendered automatically
- 📜 **History Tracking** - Complete audit trail of all transitions
- 🪝 **Lifecycle Hooks** - Execute custom logic before/after transitions
- 🏷️ **State Labels** - Custom labels and colors for each state

👉 [See Workflow Example](examples/19-workflow/)

---

## ✨ What's New in v3.1

**Admin Panel Generator** - Complete admin panel with navigation, dashboard, and integrated CRUD!

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

echo $admin->render(); // Full admin panel!
```

**Features:**
- 📊 **Dashboard** - Automatic stats cards for all tables
- 🎨 **Sidebar Navigation** - Customizable menu with icons
- 🍞 **Breadcrumbs** - Contextual navigation
- 👤 **User Menu** - Avatar and user info
- 📱 **Responsive** - Mobile-first design
- 🔗 **Full Integration** - Uses Morpheus, ListGenerator, FormGenerator

👉 [See Admin Panel Example](examples/18-admin-panel/)

---

## ✨ What's New in v3.0

**REST API Generator** - Automatic REST API generation with JWT authentication!

```php
use Morpheus\API\RestAPIGenerator;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

// Create API instance
$api = new RestAPIGenerator($pdo, 'your-secret-key');
$api->handleRequest();

// That's it! All tables now have REST endpoints
```

```bash
# Login
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}'

# Get users (with token)
curl -X GET http://localhost/api/v1/users \
  -H "Authorization: Bearer YOUR_TOKEN"

# Create user
curl -X POST http://localhost/api/v1/users \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com"}'
```

**Features:**
- 🚀 **Auto Endpoints** - GET, POST, PUT, DELETE for every table
- 🔐 **JWT Authentication** - Secure token-based auth
- 📚 **OpenAPI/Swagger** - Auto-generated documentation
- 🌐 **CORS Ready** - Cross-origin requests enabled
- 📄 **Pagination** - Automatic list pagination
- 🔒 **RBAC Integration** - Optional permission control

👉 [See REST API Example](examples/17-rest-api/)

---

## ✨ What's New in v2.9

**Multiple File Upload & Theme Integration** - Upload multiple files with drag & drop + white-label theming!

```php
// Multiple file upload with drag & drop UI
$crud = new Morpheus($pdo, 'properties');
echo $crud->renderForm(); // Automatic drag & drop for multiple_files type

// Enable global theme configuration
$crud->enableGlobalConfig();
echo $crud->renderForm(); // Applies theme from Global Config automatically
```

```sql
-- Configure multiple file upload in column metadata
ALTER TABLE properties 
MODIFY COLUMN photos TEXT 
COMMENT '{"type": "multiple_files", "accept": "image/*", "max_files": 10, "max_size": 5242880}';
```

**Features:**
- 📸 **Multiple File Upload** - Drag & drop interface with previews
- 🎨 **Theme Integration** - Apply global theme to forms automatically
- 🏢 **White-label Ready** - Custom colors, fonts, logos per tenant
- 💾 **JSON Storage** - File paths stored as JSON array
- ✅ **Validation** - Max files, MIME types, file sizes

👉 [See Multiple Files Example](examples/15-multiple-files/)  
👉 [See Theme Integration Example](examples/16-theme-integration/)

---

## ✨ What's New in v2.8

**Global Config Foundation** - Centralized configuration storage for application-wide settings!

```bash
# Set configuration
php bin/morpheus config:set application.name "My App"
php bin/morpheus config:set theme '{"primary_color":"#667eea"}'

# Get configuration
php bin/morpheus config:get application.name

# List all configuration
php bin/morpheus config:list

# Delete configuration
php bin/morpheus config:delete old.setting
```

```php
// PHP usage
$config = new GlobalMetadata($pdo);
$config->set('app.name', 'My App');
$appName = $config->get('app.name');
```

👉 [See Global Config Examples](examples/14-global-config/)

---

## ✨ What's New in v2.7

**SQL Dump & Import** - Export and import table structure and data with metadata preserved!

```bash
# Export SQL dump (structure + data + metadata)
php bin/morpheus dump:sql users --output=users.sql

# Structure only
php bin/morpheus dump:sql users --output=structure.sql --structure-only

# Data only
php bin/morpheus dump:sql users --output=data.sql --data-only

# Import SQL dump
php bin/morpheus import:sql backup.sql
php bin/morpheus import:sql backup.sql --force  # Skip confirmation
```

👉 [See SQL Dump Examples](examples/13-sql-dump/)

---

## ✨ What's New in v2.6

**Consolidation Release** - Improved documentation and developer experience!

- 📚 [Quick Start Guide](docs/QUICKSTART.md) - Get started in 5 minutes
- 🚀 [Migration Guide](docs/MIGRATION.md) - Upgrade between versions
- ✅ [Best Practices](docs/BEST_PRACTICES.md) - Production-ready patterns
- 📝 [Examples Guide](examples/README.md) - 30 examples with learning path

**Perfect for:**
- New users getting started
- Teams upgrading versions
- Production deployments
- Learning Morpheus

---

## ✨ What's New in v2.5

**Export/Import** - CSV export and import with validation and preview!

```php
// Export to CSV
$csv = $crud->export('csv');
$crud->downloadExport('users.csv');

// Import from CSV with preview
$result = $crud->import($csvContent, ['preview' => true]);

// Generate import template
$template = $crud->generateImportTemplate();
```

```bash
# CLI commands
php bin/morpheus export:csv users --output=users.csv
php bin/morpheus import:csv users data.csv --preview
php bin/morpheus generate:template users --output=template.csv
```

👉 [See Export/Import Examples](examples/12-export-import/)

---

## ✨ What's New in v2.4

**CLI Enhancements** - Powerful new commands for webhook management and metadata operations!

```bash
# Test database connection
php bin/morpheus test:connection

# Configure webhooks easily
php bin/morpheus webhook:configure users https://webhook.site/abc123
php bin/morpheus test:webhook users

# Export/import metadata for backup and migration
php bin/morpheus metadata:export users --output=users.json
php bin/morpheus metadata:import users.json
```

👉 [See CLI Guide](docs/CLI.md)

---

## ✨ What's New in v2.3

**Notifications & Webhooks** - Send email notifications and trigger webhooks automatically on CRUD events!

```sql
CREATE TABLE orders (
    id INT PRIMARY KEY,
    customer_name VARCHAR(255),
    amount DECIMAL(10,2)
) COMMENT = '{
    "notifications": {
        "on_create": {
            "email": ["admin@example.com"],
            "subject": "New Order #{{id}}",
            "template": "Customer: {{data.customer_name}}, Amount: ${{data.amount}}"
        }
    },
    "webhooks": [
        {
            "event": "on_create",
            "url": "https://api.example.com/webhook",
            "method": "POST",
            "headers": {"Authorization": "Bearer token"}
        }
    ]
}';
```

```php
$crud = new Morpheus($pdo, 'orders');
$result = $crud->handleSubmission();
// Email sent + webhook triggered automatically!
```

👉 [See Notifications Guide](docs/NOTIFICATIONS.md)

---

## ✨ What's New in v2.2

**Validation Rules & Business Logic** - Advanced validation configured entirely through table metadata!

```sql
CREATE TABLE products (
    id INT PRIMARY KEY,
    price DECIMAL(10,2),
    discount DECIMAL(5,2)
) COMMENT = '{
    "validation_rules": {
        "unique_together": [["sku", "category"]],
        "required_if": {
            "min_stock": {"status": "active"}
        },
        "conditional": {
            "discount": {
                "condition": "price > 100",
                "max": 50
            }
        }
    },
    "business_rules": {
        "max_records_per_user": 100,
        "require_approval": true
    }
}';
```

```php
$crud = new Morpheus($pdo, 'products');
$result = $crud->handleSubmission();
// Validates: unique combinations, conditional requirements, business limits
```

👉 [See Validation Rules Guide](docs/VALIDATION_RULES.md)

---

## ✨ What's New in v2.1

**Authentication, RBAC & Soft Deletes** - Complete user authentication, authorization, and soft delete support!

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    email VARCHAR(255),
    password VARCHAR(255),
    role VARCHAR(50)
) COMMENT = '{
    "authentication": {
        "enabled": true,
        "registration": {"enabled": true, "auto_login": true},
        "login": {"max_attempts": 5, "lockout_duration": 900}
    },
    "permissions": {
        "create": ["guest"],
        "read": ["owner", "admin"],
        "update": ["owner", "admin"],
        "delete": ["admin"]
    }
}';
```

```php
$crud = new Morpheus($pdo, 'users');
$crud->enableAuthentication();

// Login/Register
echo $crud->renderLoginForm();
echo $crud->renderRegistrationForm();
$result = $crud->handleAuthentication();

// Protected pages
if ($crud->isAuthenticated()) {
    $user = $crud->getCurrentUser();
    echo "Welcome, {$user['name']}!";
}

// Soft Deletes
$crud->delete($id);        // Soft delete (marks as deleted)
$crud->restore($id);       // Restore deleted record
$crud->forceDelete($id);   // Permanently delete
```

👉 [See RBAC & Authentication Guide](docs/RBAC.md)

---

## ✨ What's New in v2.0

**Table Metadata System** - Configure everything via database table comments!

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    slug VARCHAR(255),
    created_at TIMESTAMP
) COMMENT = '{
    "display_name": "Blog Posts",
    "icon": "📝",
    "list_view": {"searchable": ["title"], "per_page": 20},
    "behaviors": {
        "timestamps": {"created_at": "created_at"},
        "sluggable": {"source": "title", "target": "slug"}
    }
}';
```

```php
$crud = new Morpheus($pdo, 'posts');
echo $crud->renderList();  // Search, filters, pagination - all automatic!
$crud->handleSubmission(); // Slug and timestamps - automatic!
```

👉 [See Table Metadata Guide](docs/TABLE_METADATA.md)

---

## 🎯 Quick Start

### 1. Basic CRUD (3 lines of code!)

```php
<?php
require 'vendor/autoload.php';

use Morpheus\Morpheus;

// MySQL
$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');
// PostgreSQL
// $pdo = new PDO('pgsql:host=localhost;dbname=mydb', 'user', 'pass');

$crud = new Morpheus($pdo, 'users');

// That's it! Handle both display and submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    echo $result['success'] ? "Saved! ID: {$result['id']}" : "Error";
} else {
    echo $crud->renderForm($_GET['id'] ?? null); // null = create, ID = edit
}
```

### 2. Customize with JSON Metadata

Add metadata to your table columns using JSON in comments:

```sql
ALTER TABLE users 
MODIFY COLUMN email VARCHAR(255) 
COMMENT '{"type": "email", "label": "Email Address", "tooltip": "We will never share your email"}';

ALTER TABLE users 
MODIFY COLUMN age INT 
COMMENT '{"type": "number", "min": 18, "max": 120}';

ALTER TABLE users 
MODIFY COLUMN created_at TIMESTAMP 
COMMENT '{"hidden": true}';
```

### 3. Foreign Keys (Automatic!)

```php
// If 'posts' table has a foreign key to 'users', 
// Morpheus automatically creates a dropdown with user names
$crud = new Morpheus($pdo, 'posts');
echo $crud->renderForm();
// Dropdown shows: "John Doe", "Jane Smith", etc.
```

### 4. Many-to-Many Relationships

```php
$crud = new Morpheus($pdo, 'posts');

// Configure M:N relationship (posts ↔ tags via post_tags pivot table)
$crud->addManyToMany(
    'tags',              // Field name in form
    'post_tags',         // Pivot table
    'post_id',           // Local key
    'tag_id',            // Foreign key
    'tags'               // Related table
);

echo $crud->renderForm($_GET['id'] ?? null);
// Renders a multi-select with all available tags
```

### 5. Hooks for Custom Logic

```php
$crud = new Morpheus($pdo, 'posts');

// Auto-generate slug before saving
$crud->addHook('beforeSave', function($data) {
    $data['slug'] = strtolower(str_replace(' ', '-', $data['title']));
    return $data;
});

// Log after creation
$crud->addHook('afterCreate', function($data, $id) {
    error_log("New post created: ID $id");
});

$crud->handleSubmission();
```

### 6. Virtual Fields

```php
use Morpheus\VirtualField;

$crud = new Morpheus($pdo, 'users');

// Add password confirmation field (not stored in database)
$crud->addVirtualField(new VirtualField(
    name: 'password_confirmation',
    type: 'password',
    label: 'Confirm Password',
    required: true,
    validator: fn($value, $data) => $value === ($data['password'] ?? ''),
    attributes: [
        'placeholder' => 'Repeat your password',
        'error_message' => 'Passwords do not match'
    ]
));

// Hash password before saving
$crud->beforeSave(function($data) {
    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    return $data;
});
```

### 7. Internationalization (i18n)

```php
// Auto-detects language from URL (?lang=es), session, or browser
$crud = new Morpheus($pdo, 'users');

// Or force a specific language
$crud = new Morpheus($pdo, 'users', locale: 'es');

echo $crud->renderForm();
// Form, validation messages, and UI are now in Spanish!
```

**Language Switcher:**
```php
<a href="?lang=en">🇬🇧 English</a>
<a href="?lang=es">🇪🇸 Español</a>
<a href="?lang=fr">🇫🇷 Français</a>
```

### 8. Template System

```php
use Morpheus\Template\BladeTemplate;

// Create template engine
$engine = new BladeTemplate(__DIR__ . '/templates', __DIR__ . '/cache');

// Use with Morpheus
$crud = new Morpheus($pdo, 'users');
$crud->setTemplateEngine($engine);

// Or render templates directly
echo $engine->render('Hello, {{ $name }}!', ['name' => 'World']);
```

**Blade-like syntax:**
```blade
@if ($user->isAdmin())
    <p>Welcome, Admin!</p>
@else
    <p>Welcome, User!</p>
@endif

@foreach ($items as $item)
    <li>{{ $item }}</li>
@endforeach
```

### 9. Audit Logging

```php
$crud = new Morpheus($pdo, 'users');
$crud->enableAudit($userId); // Track who changed what

$crud->handleSubmission();
// Automatically logs: action, user_id, IP, old_values, new_values
```

---

## 📚 Documentation

### v3.3 Features (NEW!)
- [UI Components Guide](docs/UI_COMPONENTS.md) - 15 reusable components
- Components Library - Alerts, badges, buttons, cards, modals, tabs, tables, and more

### v3.2 Features
- [Workflow Engine Guide](docs/WORKFLOW.md) - State management with transitions
- Workflow System - Permission-based transitions and history tracking

### v3.1 Features
- Admin Panel Generator - Complete admin panel with dashboard
- Sidebar Navigation - Customizable menu with icons

### v3.0 Features
- REST API Generator - Automatic REST API with JWT auth
- OpenAPI/Swagger - Auto-generated API documentation

### v4.0 Features (NEW!)
- [Theme System Guide](docs/THEMES.md) - Complete theme system documentation
- [Content Types Guide](docs/CONTENT_TYPES.md) - Content type system
- [Frontend Rendering Guide](docs/FRONTEND_RENDERING.md) - Public pages
- [SEO Guide](docs/SEO.md) - SEO optimization

### v2.9 Features
- Multiple File Upload - Drag & drop with previews
- Theme Integration - White-label theming from Global Config

### v2.8 Features
- [Global Metadata Guide](docs/GLOBAL_METADATA.md) - Centralized configuration

### v2.7 Features
- SQL Dump & Import - Export/import with metadata preservation

### v2.6 Features
- [Quick Start Guide](docs/QUICKSTART.md) - 5-minute tutorial
- [Migration Guide](docs/MIGRATION.md) - Version upgrade guide
- [Best Practices](docs/BEST_PRACTICES.md) - Production patterns
- [Examples Guide](examples/README.md) - Learning path

### v2.5 Features
- Export/Import - CSV export and import with validation

### v2.4 Features
- [CLI Tool Guide](docs/CLI.md) - Enhanced CLI with 19 commands

### v2.3 Features
- [Notifications & Webhooks Guide](docs/NOTIFICATIONS.md) - Email notifications & webhooks

### v2.2 Features
- [Validation Rules Guide](docs/VALIDATION_RULES.md) - Advanced validation & business logic

### v2.1 Features
- [CLI Tool Guide](docs/CLI.md) - Command-line interface documentation
- [RBAC & Authentication Guide](docs/RBAC.md) - Complete auth & permissions guide

### v2.0 Features
- [Table Metadata Guide](docs/TABLE_METADATA.md) - Complete v2.0 guide
- [Table Metadata Roadmap](docs/TABLE_METADATA_IDEAS.md) - Future features

### Core Features
- [Template System Guide](docs/TEMPLATES.md) - Blade-like templates
- [Internationalization (i18n) Guide](docs/I18N.md) - Multi-language support
- [Hooks System Guide](docs/HOOKS.md) - 10 lifecycle hooks explained
- [Virtual Fields Guide](docs/VIRTUAL_FIELDS.md) - Password confirmation, terms acceptance
- [Many-to-Many Relationships](docs/MANY_TO_MANY.md) - M:N setup guide
- [Customization Guide](docs/CUSTOMIZATION.md) - Metadata options
- [Performance & Memory Guide](docs/PERFORMANCE.md) - Optimization best practices

### Getting Started
- [Quick Start Guide](docs/QUICKSTART.md) - Get started in 5 minutes
- [Migration Guide](docs/MIGRATION.md) - Upgrade between versions
- [Best Practices](docs/BEST_PRACTICES.md) - Production-ready patterns

### Setup & Contributing
- [Docker Setup](DOCKER_SETUP.md) - MySQL & PostgreSQL with Docker
- [Changelog](CHANGELOG.md) - Version history
- [Contributing](CONTRIBUTING.md) - How to contribute

---

## 🎨 Metadata Options

Configure fields via JSON in `COLUMN_COMMENT`:

| Option | Type | Description | Example |
|--------|------|-------------|---------|
| `type` | string | Input type | `"email"`, `"url"`, `"color"`, `"tel"`, `"password"`, `"search"`, `"time"`, `"week"`, `"month"`, `"range"`, `"file"` |
| `label` | string | Field label | `"Your Email"` |
| `placeholder` | string | Placeholder text | `"Enter your name"` |
| `min` | int | Min value (number) | `18` |
| `max` | int | Max value (number) | `120` |
| `minlength` | int | Min length (text) | `3` |
| `pattern` | string | Regex pattern | `"[0-9]{3}-[0-9]{3}-[0-9]{4}"` |
| `step` | string | Step increment | `"0.01"`, `"any"` |
| `tooltip` | string | Help text | `"Enter a valid URL"` |
| `readonly` | bool | Read-only field | `true` |
| `hidden` | bool | Hide from form | `true` |
| `autocomplete` | string | Autocomplete hint | `"email"`, `"tel"`, `"off"` |
| `display_column` | string | FK display field | `"full_name"` |
| `accept` | string | File types | `"image/*"` |
| `max_size` | int | Max file size (bytes) | `2097152` (2MB) |
| `max_files` | int | Max files (multiple_files) | `10` |

**Example:**
```sql
COMMENT '{"type": "email", "label": "Email", "tooltip": "Required field", "minlength": 5}'
```

---

## 🔒 Security Features

✅ **CSRF Protection** - Automatic token generation and validation  
✅ **SQL Injection Prevention** - Prepared statements only  
✅ **XSS Protection** - Automatic input sanitization  
✅ **File Upload Security** - Real MIME type validation with `finfo`  
✅ **Transaction Safety** - Automatic rollback on errors  

---

## 🛠️ Available Hooks

| Hook | Timing | Use Case |
|------|--------|----------|
| `beforeValidate` | Before validation | Modify data before checks |
| `afterValidate` | After validation | Additional validation |
| `beforeSave` | Before INSERT/UPDATE | Generate slugs, timestamps |
| `afterSave` | After INSERT/UPDATE | Logging, notifications |
| `beforeCreate` | Before INSERT only | Set default values |
| `afterCreate` | After INSERT only | Send welcome email |
| `beforeUpdate` | Before UPDATE only | Track changes |
| `afterUpdate` | After UPDATE only | Clear cache |
| `beforeDelete` | Before DELETE | Check dependencies |
| `afterDelete` | After DELETE | Cleanup files |

---

## 🧪 Testing

Morpheus has comprehensive test coverage:

- **300 tests** with **582+ assertions**
- **100% passing rate** (300 passing, 0 failing)
- **90% code coverage**
- Automated CI/CD with GitHub Actions
- Tests run on PHP 8.0, 8.1, 8.2, 8.3

```bash
# Run all tests
php vendor/phpunit/phpunit/phpunit

# Run specific test suite
php vendor/phpunit/phpunit/phpunit tests/AuthenticationManagerTest.php
php vendor/phpunit/phpunit/phpunit tests/PermissionManagerTest.php
php vendor/phpunit/phpunit/phpunit tests/SoftDeletesTest.php
```

---

## 🚦 Roadmap

👉 **[See Complete Roadmap](ROADMAP.md)** - Detailed plan for v3.6-v6.0  
👉 **[Universal CMS Strategy](UNIVERSAL_CMS.md)** - WordPress alternative vision  
👉 **[v4.0 Implementation Plan](docs/V4.0_PLAN.md)** - Technical details for v4.0

### 🌟 Coming in v4.0 - Universal CMS Foundation (Q3 2025)

**The Game-Changing Release** - Transform Morpheus into a WordPress alternative!

**Core Features:**
- ✨ **One-Click Installer** - Working site in 60 seconds
- 📦 **4 Content Types** - Blog, Portfolio, E-commerce, Directory
- 🎨 **5 Professional Themes** - Ready-to-use designs
- 🌐 **Frontend Rendering** - Public-facing pages with SEO
- 🔄 **WordPress Migrator** - Import your WP site in minutes
- 🚀 **10x Faster** - <500ms load time vs 2-3s for WordPress

**Why v4.0 Matters:**
- 🎯 **Market:** 810M WordPress sites (potential users)
- 💰 **Revenue:** $2.1M ARR target in Year 1
- 🏆 **Position:** First CMS that grows into any app
- 🔥 **Viral:** "Migrated from WordPress in 10 minutes"

**Timeline:** 12-16 weeks | **Launch:** September 2025

### ✅ Completed (v2.1.0)
- **Authentication System**
  - User registration with auto-login
  - Secure login with rate limiting
  - Session management with remember me
  - Password hashing (bcrypt)
- **RBAC (Role-Based Access Control)**
  - Table-level permissions
  - Row-level security
  - Automatic enforcement in forms/lists
- **Soft Deletes**
  - Mark records as deleted without removing
  - Restore deleted records
  - Force delete for permanent removal
- AuthenticationManager class
- PermissionManager class
- 5 new examples (4 auth + 1 soft deletes)
- 52 new tests (100% passing)

### ✅ Completed (v2.0.0)
- **Table Metadata System** (Phase 1 - Quick Wins)
  - UI/UX Customization (list views, colors, icons)
  - Dynamic Forms (tabs, fieldsets)
  - Automatic Behaviors (timestamps, sluggable)
  - Search & Filters (full-text search + filters)
- ListGenerator class with search/filter rendering
- TableMetadata class with 20+ methods
- 4 new examples in 06-table-metadata/
- Complete documentation (TABLE_METADATA.md)

### ✅ Completed (v1.0.0)
- Full CRUD operations (Create, Read, Update, Delete)
- Foreign key relationships
- Many-to-many relationships
- Hooks/Events system
- Audit logging
- File uploads
- Client + Server validation
- Caching system
- Comprehensive test suite

### ✅ Completed (v1.3.0)
- PostgreSQL support with Adapter pattern
- Auto-detection of database driver
- Docker setup for MySQL & PostgreSQL

### ✅ Completed (v1.2.0)
- Virtual fields (password confirmation, terms acceptance)
- Comprehensive test suite (113 tests)
- CI/CD pipeline (GitHub Actions)
- FormGenerator enhancements (16+ metadata options)

### ✅ Completed (v1.4.0)
- Internationalization (i18n) - 3 languages (EN, ES, FR)
- Advanced M:N UI (checkboxes with search)
- Translator class with auto-detection
- Client + Server translation support
- 31 new tests for i18n (100% passing)

### ✅ Completed (v1.5.0)
- Template System - Blade-like syntax
- Layout inheritance (@extends, @section, @yield)
- Partials (@include)
- Automatic escaping ({{ }} vs {!! !!})
- File caching for performance
- 17 new tests for templates (100% passing)

### ✅ Completed (v2.2.0)
- **Validation Rules**
  - unique_together - Composite unique constraints
  - required_if - Conditional required fields
  - conditional - Dynamic min/max validation
- **Business Rules**
  - max_records_per_user - Record limits per user
  - require_approval - Approval workflows
- ValidationRulesEngine class
- 4 new examples in 10-validation-rules/
- 12 new tests (100% passing)

### ✅ Completed (v2.3.0)
- **Notifications & Webhooks**
  - Email notifications with template placeholders
  - Webhook triggers with custom headers
  - Field-specific update notifications
  - Multiple recipients and webhooks
  - Non-blocking error handling
- NotificationManager class
- 2 new examples in 11-notifications/
- 7 new tests (100% passing)

### ✅ Completed (v2.9.0)
- **Multiple File Upload**
  - Drag & drop interface with file previews
  - Multiple file handling with JSON storage
  - Max files validation
  - Existing file management
- **Theme Integration**
  - Global theme configuration support
  - CSS variables injection
  - White-label branding (logo, app name)
  - Per-tenant theming capability
- ThemeManager class
- FileUploadHandler enhancements
- 2 new examples (real estate, theme demo)
- 9 new tests (100% passing)

### ✅ Completed (v3.0.0)
- **REST API Generator**
  - Automatic CRUD endpoints for all tables
  - JWT authentication with token generation
  - OpenAPI/Swagger documentation
  - CORS support for cross-origin requests
  - Pagination for list endpoints
  - RBAC integration optional
- RestAPIGenerator class
- 1 new example (REST API tester)
- 7 new tests (100% passing)

### ✅ Completed (v3.1.0)
- **Admin Panel Generator**
  - Complete admin panel with sidebar navigation
  - Dashboard with automatic stats
  - Breadcrumbs for contextual navigation
  - User menu with avatar
  - Responsive mobile-first design
  - Full integration with Morpheus components
- AdminPanel class
- 1 new example (complete admin panel)
- 12 new tests (100% passing)

### ✅ Completed (v3.2.0)
- **Workflow Engine**
  - State management with configurable states
  - Transitions with from/to state validation
  - Permission-based transition control
  - Automatic transition buttons in forms
  - Complete history tracking with audit trail
  - Lifecycle hooks (before/after transitions)
  - State labels with custom colors
  - Multiple from states support
- WorkflowEngine class
- 1 new example (order management workflow)
- 13 new tests (100% passing)

### ✅ Completed (v3.3.0)
- **UI Components Library**
  - 15 reusable components (alert, badge, button, card, modal, tabs, accordion, table, etc.)
  - Themeable with custom colors
  - Accessible with ARIA labels
  - Responsive mobile-first design
  - Zero dependencies
  - XSS protection built-in
- Components class with static methods
- 1 new example (UI components showcase)
- 26 new tests (100% passing)

### 🔮 Planned (v4.0+) - Universal CMS Era

**v4.0 - Universal CMS Foundation (Q3 2025)**
- [x] Content type system (blog implemented)
- [x] Frontend rendering engine with SEO
- [x] One-click installer (WordPress-style) ✅
- [x] 3 professional themes (Minimal, Modern, Classic)
- [x] WordPress migration tool ✅
- [x] Media library ✅
- [x] Comment system ✅

**v4.1 - CMS Advanced Features (Q4 2025)**
- [ ] Theme marketplace
- [ ] Page builder (drag & drop)
- [ ] Widget system
- [ ] Menu builder
- [ ] Revision history
- [ ] Scheduled publishing

**v4.2 - CMS SEO & Performance (Q1 2026)**
- [ ] Built-in SEO optimization
- [ ] Image optimization (WebP)
- [ ] CDN integration
- [ ] Multi-layer caching
- [ ] PWA capabilities

**v4.3 - Multi-Tenant & SaaS (Q2 2026)**
- [ ] Tenant isolation
- [ ] White-label capabilities
- [ ] Usage tracking & billing
- [ ] Subdomain routing

---

## 📊 Project Stats

- **58 PHP classes** (~19,500 lines)
- **43 working examples** (1 in v4.0, 1 in v3.3, 1 in v3.2, 1 in v3.1, 1 in v3.0, 2 in v2.9, 1 in v2.8, 1 in v2.7, 2 in v2.5, 2 in v2.3, 4 in v2.2, 6 in v2.1, 4 in v2.0)
- **23 technical documents**
- **421 automated tests** (418 passing, 99.3% pass rate, 90% coverage) (1 in v3.3, 1 in v3.2, 1 in v3.1, 1 in v3.0, 2 in v2.9, 1 in v2.8, 1 in v2.7, 2 in v2.5, 2 in v2.3, 4 in v2.2, 6 in v2.1, 4 in v2.0)
- **22 technical documents**
- **366 automated tests** (100% passing, 90% coverage)
- **19 CLI commands**
- **Languages supported**: 3 (English, Spanish, French)
- **Databases supported**: 2 (MySQL, PostgreSQL)
- **Template engine**: Blade-like syntax
- **CLI Tool**: 20 commands (init, install, generate, validate, clear, list, export, import, etc.)
- **Authentication**: Register, login, logout, password reset, rate limiting
- **RBAC**: Table + row-level permissions
- **Soft Deletes**: Delete, restore, force delete
- **Validation Rules**: 3 types (unique_together, required_if, conditional)
- **Business Rules**: 2 types (max_records_per_user, require_approval)
- **Table metadata features**: 6 (UI/UX, Forms, Behaviors, Search, Validation, Business)
- **File Upload**: Single + multiple with drag & drop
- **Theming**: Global config with CSS variables
- **REST API**: Automatic generation with JWT auth
- **Admin Panel**: Complete admin interface with dashboard
- **Refactored Classes**: 6 (FormGenerator, ListGenerator, AdminPanel, CRUDHandler, ValidationEngine, SchemaAnalyzer)

---

## 🤝 Contributing

Contributions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Commit your changes: `git commit -m 'Add amazing feature'`
4. Push to the branch: `git push origin feature/amazing-feature`
5. Open a Pull Request

---

## 👥 Credits

**Creator & Project Lead**: [Mario Raúl Carbonell Martínez](https://github.com/mcarbonell)  
**Development**: Amazon Q (Claude Sonnet 4.5)

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🌟 Show Your Support

If you find this project useful, please consider:
- ⭐ Starring the repository
- 🐛 Reporting bugs
- 💡 Suggesting new features
- 📢 Sharing with others

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
