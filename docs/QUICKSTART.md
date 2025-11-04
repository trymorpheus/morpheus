# Quick Start Guide

Get started with DynamicCRUD in 5 minutes!

---

## Installation

```bash
composer require dynamiccrud/dynamiccrud
```

**Requirements:** PHP 8.0+, MySQL 5.7+ or PostgreSQL 12+

---

## Your First CRUD (3 lines!)

```php
<?php
require 'vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');
$crud = new DynamicCRUD($pdo, 'users');

// Display form
echo $crud->renderForm($_GET['id'] ?? null);

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    echo $result['success'] ? "✅ Saved!" : "❌ Error";
}
```

**That's it!** You now have a fully functional CRUD with validation, CSRF protection, and security.

---

## 5-Minute Tutorial

### Step 1: Database Setup

```sql
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Step 2: Basic CRUD

```php
$crud = new DynamicCRUD($pdo, 'products');

// Create/Edit form
echo $crud->renderForm($_GET['id'] ?? null);

// Handle submission
if ($_POST) {
    $result = $crud->handleSubmission();
}
```

### Step 3: Add Metadata (Optional)

```sql
ALTER TABLE products 
MODIFY COLUMN price DECIMAL(10,2) 
COMMENT '{"type": "number", "min": 0, "step": "0.01"}';
```

### Step 4: List View

```php
echo $crud->renderList();
```

**Done!** You have a complete CRUD with list, create, edit, and delete.

---

## Common Use Cases

### Use Case 1: Admin Panel

```php
// index.php
$tables = ['users', 'posts', 'categories'];

foreach ($tables as $table) {
    echo "<h2>$table</h2>";
    $crud = new DynamicCRUD($pdo, $table);
    echo $crud->renderList();
}
```

### Use Case 2: User Registration

```php
$crud = new DynamicCRUD($pdo, 'users');
$crud->enableAuthentication();

// Registration form
echo $crud->renderRegistrationForm();

// Handle registration
if ($_POST['action'] === 'register') {
    $result = $crud->handleAuthentication();
}
```

### Use Case 3: Data Export

```php
$crud = new DynamicCRUD($pdo, 'orders');

// Export to CSV
$crud->downloadExport('orders.csv');

// Or get CSV string
$csv = $crud->export('csv');
```

### Use Case 4: Bulk Import

```php
$crud = new DynamicCRUD($pdo, 'products');

// Preview import
$result = $crud->import($csvContent, ['preview' => true]);

// Import with error handling
$result = $crud->import($csvContent, ['skip_errors' => true]);
```

---

## Next Steps

### Add Relationships

```php
// Foreign key (automatic)
$crud = new DynamicCRUD($pdo, 'posts');
// If posts.user_id references users.id, dropdown is automatic!

// Many-to-many
$crud->addManyToMany('tags', 'post_tags', 'post_id', 'tag_id', 'tags');
```

### Add Hooks

```php
$crud->beforeSave(function($data) {
    $data['slug'] = strtolower(str_replace(' ', '-', $data['title']));
    return $data;
});

$crud->afterCreate(function($id, $data) {
    // Send notification, log, etc.
});
```

### Add Validation Rules

```sql
ALTER TABLE products COMMENT = '{
    "validation_rules": {
        "unique_together": [["sku", "category"]],
        "conditional": {
            "discount": {
                "condition": "price > 100",
                "max": 50
            }
        }
    }
}';
```

### Enable Notifications

```sql
ALTER TABLE orders COMMENT = '{
    "notifications": {
        "on_create": {
            "email": ["admin@example.com"],
            "subject": "New Order #{{id}}"
        }
    },
    "webhooks": [{
        "event": "on_create",
        "url": "https://api.example.com/webhook"
    }]
}';
```

---

## CLI Tools

```bash
# Initialize project
php bin/dynamiccrud init

# List tables
php bin/dynamiccrud list:tables

# Generate metadata
php bin/dynamiccrud generate:metadata products

# Export data
php bin/dynamiccrud export:csv products --output=products.csv

# Import data
php bin/dynamiccrud import:csv products data.csv --preview

# Test webhook
php bin/dynamiccrud test:webhook orders
```

---

## Troubleshooting

### Forms not showing?

Check your database connection:
```bash
php bin/dynamiccrud test:connection
```

### Validation not working?

Clear cache:
```bash
php bin/dynamiccrud clear:cache
```

### Need help?

1. Check [Full Documentation](../README.md)
2. See [Examples](../examples/)
3. Read [Guides](.)

---

## What's Next?

- [Table Metadata Guide](TABLE_METADATA.md) - Configure everything via database
- [RBAC Guide](RBAC.md) - Add authentication and permissions
- [Validation Rules](VALIDATION_RULES.md) - Advanced validation
- [Notifications](NOTIFICATIONS.md) - Email and webhooks
- [CLI Guide](CLI.md) - Command-line tools
- [All Examples](../examples/) - 30 working examples

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
