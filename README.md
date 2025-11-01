# DynamicCRUD

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
- **Hooks/Events system** (10 lifecycle hooks)
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

**Requirements:** PHP 8.0+, MySQL 5.7+, PDO extension

---

## 🎯 Quick Start

### 1. Basic CRUD (3 lines of code!)

```php
<?php
require 'vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');
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

### 6. Audit Logging

```php
$crud = new DynamicCRUD($pdo, 'users');
$crud->enableAudit($userId); // Track who changed what

$crud->handleSubmission();
// Automatically logs: action, user_id, IP, old_values, new_values
```

---

## 📚 Documentation

- [Hooks System Guide](docs/HOOKS.md) - 10 lifecycle hooks explained
- [Many-to-Many Relationships](docs/MANY_TO_MANY.md) - M:N setup guide
- [Customization Guide](docs/CUSTOMIZATION.md) - Metadata options
- [Changelog](CHANGELOG.md) - Version history
- [Contributing](CONTRIBUTING.md) - How to contribute

---

## 🎨 Metadata Options

Configure fields via JSON in `COLUMN_COMMENT`:

| Option | Type | Description | Example |
|--------|------|-------------|---------|
| `type` | string | Input type | `"email"`, `"url"`, `"number"`, `"file"` |
| `label` | string | Field label | `"Your Email"` |
| `min` | int | Min value (number) | `18` |
| `max` | int | Max value (number) | `120` |
| `minlength` | int | Min length (text) | `3` |
| `tooltip` | string | Help text | `"Enter a valid URL"` |
| `hidden` | bool | Hide from form | `true` |
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

### 🔮 Planned (v2.0+)
- [ ] PostgreSQL support
- [ ] Advanced M:N UI (checkboxes, search)
- [ ] Virtual fields (password confirmation)
- [ ] Internationalization (i18n)
- [ ] REST API generation
- [ ] GraphQL support

---

## 📊 Project Stats

- **10 PHP classes** (~3,500 lines)
- **8 working examples**
- **7 technical documents**
- **Development time**: < 1 day
- **Test coverage**: 98.75%

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
