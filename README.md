# DynamicCRUD

[![Tests](https://github.com/mcarbonell/DynamicCRUD/workflows/Tests/badge.svg)](https://github.com/mcarbonell/DynamicCRUD/actions)
[![Code Quality](https://github.com/mcarbonell/DynamicCRUD/workflows/Code%20Quality/badge.svg)](https://github.com/mcarbonell/DynamicCRUD/actions)
[![Packagist Version](https://img.shields.io/packagist/v/dynamiccrud/dynamiccrud)](https://packagist.org/packages/dynamiccrud/dynamiccrud)
[![PHP Version](https://img.shields.io/packagist/php-v/dynamiccrud/dynamiccrud)](https://packagist.org/packages/dynamiccrud/dynamiccrud)
[![License](https://img.shields.io/github/license/mcarbonell/DynamicCRUD)](https://github.com/mcarbonell/DynamicCRUD/blob/main/LICENSE)
[![Downloads](https://img.shields.io/packagist/dt/dynamiccrud/dynamiccrud)](https://packagist.org/packages/dynamiccrud/dynamiccrud)

**A powerful PHP library that automatically generates CRUD forms with validation based on your database structure.**

Stop writing repetitive CRUD code. DynamicCRUD analyzes your MySQL schema and creates fully functional forms with validation, security, and advanced features out of the box.

[🇪🇸 Documentación en Español](README.es.md)

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
composer require dynamiccrud/dynamiccrud
```

**Requirements:** PHP 8.0+, MySQL 5.7+ or PostgreSQL 12+, PDO extension

---

## 🎯 Quick Start

### 1. Basic CRUD (3 lines of code!)

```php
<?php
require 'vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

// MySQL
$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');
// PostgreSQL
// $pdo = new PDO('pgsql:host=localhost;dbname=mydb', 'user', 'pass');

$crud = new DynamicCRUD($pdo, 'users');

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
// DynamicCRUD automatically creates a dropdown with user names
$crud = new DynamicCRUD($pdo, 'posts');
echo $crud->renderForm();
// Dropdown shows: "John Doe", "Jane Smith", etc.
```

### 4. Many-to-Many Relationships

```php
$crud = new DynamicCRUD($pdo, 'posts');

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
$crud = new DynamicCRUD($pdo, 'posts');

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
use DynamicCRUD\VirtualField;

$crud = new DynamicCRUD($pdo, 'users');

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
$crud = new DynamicCRUD($pdo, 'users');

// Or force a specific language
$crud = new DynamicCRUD($pdo, 'users', locale: 'es');

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
use DynamicCRUD\Template\BladeTemplate;

// Create template engine
$engine = new BladeTemplate(__DIR__ . '/templates', __DIR__ . '/cache');

// Use with DynamicCRUD
$crud = new DynamicCRUD($pdo, 'users');
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
$crud = new DynamicCRUD($pdo, 'users');
$crud->enableAudit($userId); // Track who changed what

$crud->handleSubmission();
// Automatically logs: action, user_id, IP, old_values, new_values
```

---

## 📚 Documentation

- [Template System Guide](docs/TEMPLATES.md) - Blade-like templates
- [Internationalization (i18n) Guide](docs/I18N.md) - Multi-language support
- [Hooks System Guide](docs/HOOKS.md) - 10 lifecycle hooks explained
- [Virtual Fields Guide](docs/VIRTUAL_FIELDS.md) - Password confirmation, terms acceptance
- [Many-to-Many Relationships](docs/MANY_TO_MANY.md) - M:N setup guide
- [Customization Guide](docs/CUSTOMIZATION.md) - Metadata options
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

DynamicCRUD has comprehensive test coverage:

- **195 tests** with **340+ assertions**
- **76% passing rate** (149 passing, 40 failing, 6 skipped)
- Automated CI/CD with GitHub Actions
- Tests run on PHP 8.0, 8.1, 8.2, 8.3

```bash
# Run tests
vendor/bin/phpunit

# Run with detailed output
vendor/bin/phpunit --testdox
```

---

## 🚦 Roadmap

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

### 🔮 Planned (v2.0+)
- [ ] SQL Server support
- [ ] REST API generation
- [ ] GraphQL support
- [ ] More languages (DE, IT, PT)
- [ ] Advanced template features

---

## 📊 Project Stats

- **16 PHP classes** (~5,000 lines)
- **11 working examples**
- **10 technical documents**
- **195 automated tests** (76% passing)
- **Languages supported**: 3 (English, Spanish, French)
- **Databases supported**: 2 (MySQL, PostgreSQL)
- **Template engine**: Blade-like syntax
- **Development time**: < 2 days

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
**Development**: Amazon Q, Gemini 2.5 Pro

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
