# DynamicCRUD Examples

30 working examples demonstrating all features from basic to advanced.

---

## Quick Start

```bash
# 1. Setup database
mysql -u root -p test < setup/mysql.sql

# 2. Start PHP server
php -S localhost:8000

# 3. Open browser
open http://localhost:8000/index.html
```

---

## Examples by Category

### 01. Basic CRUD
**Learn:** Core functionality, zero configuration

- `index.php` - Complete CRUD in 3 lines of code

**Key Concepts:**
- Automatic form generation
- Built-in validation
- CSRF protection

---

### 02. Relationships
**Learn:** Foreign keys and many-to-many relationships

- `foreign-keys.php` - Automatic dropdown for FK
- `many-to-many.php` - Multi-select with pivot table sync

**Key Concepts:**
- Auto-detection of foreign keys
- Custom display columns
- Pivot table management

---

### 03. Customization
**Learn:** Field metadata and advanced inputs

- `metadata.php` - 16+ metadata options
- `advanced-inputs.php` - HTML5 input types
- `file-uploads.php` - Secure file handling

**Key Concepts:**
- JSON metadata in column comments
- Input type customization
- File validation

---

### 04. Advanced Features
**Learn:** Hooks, virtual fields, validation

- `hooks.php` - 10 lifecycle hooks
- `virtual-fields.php` - Password confirmation, terms
- `validation.php` - Custom validation rules

**Key Concepts:**
- Event-driven architecture
- Non-database fields
- Custom validators

---

### 05. Production Features
**Learn:** i18n, templates, audit logging

- `i18n.php` - Multi-language support (EN, ES, FR)
- `templates.php` - Blade-like templates
- `audit.php` - Change tracking

**Key Concepts:**
- Auto-detection of locale
- Template inheritance
- Audit trails

---

### 06. Table Metadata (v2.0)
**Learn:** Configure everything via database

- `ui-customization.php` - List views, search, pagination
- `dynamic-forms.php` - Tabbed forms, fieldsets
- `automatic-behaviors.php` - Auto-timestamps, slugs
- `search-filters.php` - Advanced search and filters

**Key Concepts:**
- Zero PHP configuration
- Metadata-driven UI
- Automatic behaviors

---

### 07. RBAC (v2.1)
**Learn:** Permissions and access control

- `basic-rbac.php` - Table and row-level permissions
- `list-with-permissions.php` - Dynamic action buttons

**Key Concepts:**
- Role-based access control
- Owner-based security
- Permission enforcement

---

### 08. Authentication (v2.1)
**Learn:** User authentication system

- `login.php` - Login with rate limiting
- `register.php` - User registration
- `dashboard.php` - Protected pages
- `forgot-password.php` - Password reset flow

**Key Concepts:**
- Session management
- Rate limiting
- Password hashing

---

### 09. Soft Deletes (v2.1)
**Learn:** Non-destructive deletion

- `index.php` - Delete, restore, force delete

**Key Concepts:**
- Soft delete behavior
- Restore functionality
- Permanent deletion

---

### 10. Validation Rules (v2.2)
**Learn:** Advanced validation and business logic

- `unique-together.php` - Composite unique constraints
- `required-if.php` - Conditional required fields
- `conditional.php` - Dynamic min/max validation
- `business-rules.php` - Record limits, approval workflows

**Key Concepts:**
- Metadata-driven validation
- Business rule enforcement
- Complex validation logic

---

### 11. Notifications & Webhooks (v2.3)
**Learn:** Email and webhook triggers

- `email-notifications.php` - Email on CRUD events
- `webhooks.php` - Webhook triggers

**Key Concepts:**
- Template placeholders
- Event-based notifications
- Webhook integration

---

### 12. Export/Import (v2.5)
**Learn:** CSV export and import

- `export.php` - Export data to CSV
- `import.php` - Import with validation and preview

**Key Concepts:**
- CSV generation
- Import validation
- Preview mode

---

## Running Examples

### Method 1: PHP Built-in Server

```bash
cd examples
php -S localhost:8000
```

Open http://localhost:8000/index.html

### Method 2: Apache/Nginx

Point document root to `examples/` directory.

### Method 3: Docker

```bash
docker-compose up
```

---

## Database Setup

### MySQL

```bash
mysql -u root -p test < setup/mysql.sql
```

### PostgreSQL

```bash
psql -U postgres -d test -f setup/postgresql.sql
```

---

## Example Structure

Each example follows this pattern:

```
example-name.php
├── Database connection
├── DynamicCRUD initialization
├── Configuration (hooks, metadata, etc.)
├── Form rendering or list display
└── Submission handling
```

---

## Common Patterns

### Pattern 1: Form with Submission

```php
$crud = new DynamicCRUD($pdo, 'table');

if ($_POST) {
    $result = $crud->handleSubmission();
    // Handle result
}

echo $crud->renderForm($_GET['id'] ?? null);
```

### Pattern 2: List with Actions

```php
$crud = new DynamicCRUD($pdo, 'table');

echo $crud->renderList();
```

### Pattern 3: Export/Import

```php
$crud = new DynamicCRUD($pdo, 'table');

// Export
$crud->downloadExport('file.csv');

// Import
$result = $crud->import($csvContent);
```

---

## Troubleshooting

### Database Connection Error

Check credentials in examples:
```php
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
```

### Tables Not Found

Run setup script:
```bash
mysql -u root -p test < setup/mysql.sql
```

### Port Already in Use

Use different port:
```bash
php -S localhost:8001
```

### Cache Issues

Clear cache:
```bash
php ../bin/dynamiccrud clear:cache
```

---

## Learning Path

**Beginner:**
1. 01-basic → 02-relationships → 03-customization

**Intermediate:**
2. 04-advanced → 05-features → 06-table-metadata

**Advanced:**
3. 07-rbac → 08-authentication → 10-validation-rules

**Production:**
4. 11-notifications → 12-export-import

---

## Next Steps

After exploring examples:

1. Read [Quick Start Guide](../docs/QUICKSTART.md)
2. Check [Best Practices](../docs/BEST_PRACTICES.md)
3. Review [Full Documentation](../README.md)
4. Build your own application!

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
