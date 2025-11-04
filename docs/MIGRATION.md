# Migration Guide

Guide for upgrading between DynamicCRUD versions.

---

## Upgrading to v2.5 (Export/Import)

**New Features:**
- CSV export/import functionality
- 3 new CLI commands

**Breaking Changes:** None

**Migration Steps:**
```bash
composer update dynamiccrud/dynamiccrud
php bin/dynamiccrud clear:cache
```

**New Methods:**
```php
// Export
$csv = $crud->export('csv');
$crud->downloadExport('file.csv');

// Import
$result = $crud->import($csvContent);
$template = $crud->generateImportTemplate();
```

---

## Upgrading to v2.4 (CLI Enhancements)

**New Features:**
- 5 new CLI commands
- Webhook configuration from CLI
- Metadata export/import

**Breaking Changes:** None

**Migration Steps:**
```bash
composer update dynamiccrud/dynamiccrud
```

**New CLI Commands:**
```bash
php bin/dynamiccrud test:connection
php bin/dynamiccrud webhook:configure users https://webhook.site/abc
php bin/dynamiccrud test:webhook users
php bin/dynamiccrud metadata:export users --output=users.json
php bin/dynamiccrud metadata:import users.json
```

---

## Upgrading to v2.3 (Notifications & Webhooks)

**New Features:**
- Email notifications
- Webhook triggers
- Template placeholders

**Breaking Changes:** None

**Migration Steps:**
```bash
composer update dynamiccrud/dynamiccrud
php bin/dynamiccrud clear:cache
```

**Configuration:**
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
        "url": "https://webhook.site/abc123"
    }]
}';
```

---

## Upgrading to v2.2 (Validation Rules)

**New Features:**
- Advanced validation rules
- Business rules
- ValidationRulesEngine

**Breaking Changes:** None

**Migration Steps:**
```bash
composer update dynamiccrud/dynamiccrud
php bin/dynamiccrud clear:cache
```

**Configuration:**
```sql
ALTER TABLE products COMMENT = '{
    "validation_rules": {
        "unique_together": [["sku", "category"]],
        "required_if": {
            "phone": {"status": "active"}
        },
        "conditional": {
            "discount": {
                "condition": "price > 100",
                "max": 50
            }
        }
    },
    "business_rules": {
        "max_records_per_user": 100
    }
}';
```

---

## Upgrading to v2.1 (Authentication & RBAC)

**New Features:**
- Authentication system
- RBAC with row-level security
- Soft deletes

**Breaking Changes:** None

**Migration Steps:**

1. Update package:
```bash
composer update dynamiccrud/dynamiccrud
```

2. Add authentication metadata:
```sql
ALTER TABLE users COMMENT = '{
    "authentication": {
        "enabled": true,
        "registration": {"enabled": true}
    },
    "permissions": {
        "create": ["guest"],
        "read": ["owner", "admin"],
        "update": ["owner", "admin"],
        "delete": ["admin"]
    }
}';
```

3. Enable authentication:
```php
$crud->enableAuthentication();
```

4. For soft deletes, add column:
```sql
ALTER TABLE posts ADD COLUMN deleted_at TIMESTAMP NULL;

ALTER TABLE posts COMMENT = '{
    "behaviors": {
        "soft_deletes": {"column": "deleted_at"}
    }
}';
```

---

## Upgrading to v2.0 (Table Metadata)

**New Features:**
- Table metadata system
- ListGenerator
- Automatic behaviors

**Breaking Changes:** None

**Migration Steps:**

1. Update package:
```bash
composer update dynamiccrud/dynamiccrud
```

2. Clear cache:
```bash
php bin/dynamiccrud clear:cache
```

3. Add table metadata (optional):
```sql
ALTER TABLE posts COMMENT = '{
    "display_name": "Blog Posts",
    "icon": "📝",
    "list_view": {
        "searchable": ["title", "content"],
        "per_page": 20
    },
    "behaviors": {
        "timestamps": {
            "created_at": "created_at",
            "updated_at": "updated_at"
        },
        "sluggable": {
            "source": "title",
            "target": "slug"
        }
    }
}';
```

4. Use new list view:
```php
echo $crud->renderList();
```

---

## Upgrading from v1.x to v2.0

**Major Changes:**
- Table metadata system introduced
- New ListGenerator class
- Enhanced CLI tool

**Breaking Changes:** None (fully backward compatible)

**Recommended Migration:**

1. Update package:
```bash
composer update dynamiccrud/dynamiccrud
```

2. Generate metadata for existing tables:
```bash
php bin/dynamiccrud generate:metadata users
php bin/dynamiccrud generate:metadata posts
```

3. Apply generated SQL to database

4. Clear cache:
```bash
php bin/dynamiccrud clear:cache
```

5. Test your application

---

## Version Compatibility Matrix

| Version | PHP | MySQL | PostgreSQL | Features |
|---------|-----|-------|------------|----------|
| 2.5.x | 8.0+ | 5.7+ | 12+ | Export/Import |
| 2.4.x | 8.0+ | 5.7+ | 12+ | CLI Enhanced |
| 2.3.x | 8.0+ | 5.7+ | 12+ | Notifications |
| 2.2.x | 8.0+ | 5.7+ | 12+ | Validation Rules |
| 2.1.x | 8.0+ | 5.7+ | 12+ | Auth & RBAC |
| 2.0.x | 8.0+ | 5.7+ | 12+ | Table Metadata |
| 1.x | 8.0+ | 5.7+ | 12+ | Core CRUD |

---

## Common Migration Issues

### Issue: Cache not clearing

**Solution:**
```bash
rm -rf cache/*
php bin/dynamiccrud clear:cache
```

### Issue: Metadata not loading

**Solution:**
```sql
-- Check table comment
SELECT TABLE_COMMENT FROM information_schema.TABLES 
WHERE TABLE_NAME = 'your_table';

-- Validate JSON
php bin/dynamiccrud validate:metadata your_table
```

### Issue: Tests failing after upgrade

**Solution:**
```bash
# Clear cache
php bin/dynamiccrud clear:cache

# Run tests
php vendor/phpunit/phpunit/phpunit

# Check specific test
php vendor/phpunit/phpunit/phpunit tests/YourTest.php
```

### Issue: Permissions not working

**Solution:**
```php
// Ensure authentication is enabled
$crud->enableAuthentication();

// Set current user
$crud->setCurrentUser($userId, $role);

// Check permissions
$pm = $crud->getPermissionManager();
var_dump($pm->canCreate());
```

---

## Rollback Instructions

If you need to rollback to a previous version:

```bash
# Rollback to specific version
composer require dynamiccrud/dynamiccrud:^2.4.0

# Clear cache
php bin/dynamiccrud clear:cache

# Test application
```

---

## Getting Help

- [Documentation](../README.md)
- [Examples](../examples/)
- [GitHub Issues](https://github.com/mcarbonell/DynamicCRUD/issues)

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
