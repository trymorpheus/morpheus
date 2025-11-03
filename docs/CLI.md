# CLI Tool Guide

**DynamicCRUD v2.1+**

## Overview

The DynamicCRUD CLI Tool provides command-line utilities for project management, metadata generation, and cache management. It significantly improves developer experience by automating common tasks.

## Installation

The CLI tool is automatically available after installing DynamicCRUD via Composer:

```bash
composer require dynamiccrud/dynamiccrud
```

The `dynamiccrud` command will be available in `vendor/bin/`:

```bash
php vendor/bin/dynamiccrud --help
```

---

## Available Commands

### 1. init - Initialize Project

Initialize DynamicCRUD in your project with interactive database configuration.

**Usage:**
```bash
php vendor/bin/dynamiccrud init
```

**What it does:**
- Creates `dynamiccrud.json` configuration file
- Prompts for database credentials interactively
- Supports MySQL and PostgreSQL

**Interactive Prompts:**
```
Driver (mysql/pgsql) [mysql]: mysql
Host [localhost]: localhost
Database name [test]: myapp
Username [root]: root
Password []: mypassword
```

**Generated Config (`dynamiccrud.json`):**
```json
{
    "driver": "mysql",
    "host": "localhost",
    "database": "myapp",
    "username": "root",
    "password": "mypassword"
}
```

---

### 2. generate:metadata - Generate Metadata

Auto-generate metadata JSON from existing table schema.

**Usage:**
```bash
php vendor/bin/dynamiccrud generate:metadata <table>
```

**Example:**
```bash
php vendor/bin/dynamiccrud generate:metadata users
```

**What it does:**
- Analyzes table schema (columns, types, constraints)
- Detects common patterns:
  - Timestamps (`created_at`, `updated_at`)
  - Sluggable (`slug` + `title` columns)
  - Soft deletes (`deleted_at` column)
  - Searchable fields (varchar/text columns)
- Generates metadata JSON
- Provides ready-to-run SQL ALTER statement

**Output Example:**
```
============================================================
Generated Metadata:
============================================================

{
    "display_name": "Users",
    "icon": "📄",
    "behaviors": {
        "timestamps": {
            "created_at": "created_at",
            "updated_at": "updated_at"
        }
    },
    "list_view": {
        "searchable": ["name", "email"]
    }
}

============================================================
SQL Statement:
============================================================

ALTER TABLE users COMMENT = '{"display_name":"Users",...}';

✅ Metadata generated successfully
ℹ️  Copy the SQL statement above and run it on your database
```

**Auto-Detection Rules:**

| Pattern | Detection | Generated Metadata |
|---------|-----------|-------------------|
| `created_at` or `updated_at` columns | Timestamps | `behaviors.timestamps` |
| `slug` + `title` columns | Sluggable | `behaviors.sluggable` |
| `deleted_at` column | Soft Deletes | `behaviors.soft_deletes` |
| `varchar`/`text` columns | Searchable | `list_view.searchable` |

---

### 3. validate:metadata - Validate Metadata

Validate and display table metadata configuration.

**Usage:**
```bash
php vendor/bin/dynamiccrud validate:metadata <table>
```

**Example:**
```bash
php vendor/bin/dynamiccrud validate:metadata posts
```

**What it does:**
- Reads table metadata from database
- Validates JSON structure
- Displays enabled features
- Shows configuration status

**Output Example:**
```
============================================================
Metadata Validation Results:
============================================================

  ✅ Display Name:             Blog Posts
  ✅ Icon:                     📝
  ✅ Timestamps:               Enabled
  ✅ Sluggable:                Enabled
  ⚪ Soft Deletes:             Disabled
  ✅ Permissions:              Enabled
  ✅ Row-Level Security:       Enabled
  ✅ Authentication:           Disabled
  ✅ Searchable Fields:        Enabled

✅ Metadata is valid
```

**Status Indicators:**
- ✅ = Feature enabled/configured
- ⚪ = Feature disabled/not configured

---

### 4. list:tables - List All Tables

Display all database tables with metadata information.

**Usage:**
```bash
php vendor/bin/dynamiccrud list:tables
```

**What it does:**
- Lists all tables in database
- Shows which tables have metadata
- Displays enabled features per table
- Provides quick project overview

**Output Example:**
```
================================================================================
Table                          Has Metadata    Features                           
================================================================================
users                          Yes             Timestamps, Search                 
posts                          Yes             Timestamps, Sluggable, RBAC, Search
comments                       Yes             RBAC                               
categories                     No                                                 
tags                           No                                                 
================================================================================

✅ 5 tables found
```

**Feature Abbreviations:**
- **Timestamps** - Auto-timestamps enabled
- **Sluggable** - Auto-slug generation
- **Soft Deletes** - Soft delete support
- **RBAC** - Permissions configured
- **Auth** - Authentication enabled
- **Search** - Searchable fields configured

---

### 5. clear:cache - Clear Cache

Clear schema and template cache files.

**Usage:**
```bash
php vendor/bin/dynamiccrud clear:cache
```

**What it does:**
- Deletes template cache files (`cache/templates/*`)
- Deletes schema cache files (`cache/*.cache`)
- Reports number of files deleted

**Output Example:**
```
ℹ️  Clearing cache...
✅ Cache cleared: 15 files deleted
```

**When to use:**
- After changing table metadata
- After modifying templates
- When debugging schema issues
- After database schema changes

---

## Configuration File

The `dynamiccrud.json` file stores database connection settings.

**Location:** Project root directory

**Format:**
```json
{
    "driver": "mysql",
    "host": "localhost",
    "database": "myapp",
    "username": "root",
    "password": "secret"
}
```

**Supported Drivers:**
- `mysql` - MySQL 5.7+
- `pgsql` - PostgreSQL 12+

**Security Note:** Add `dynamiccrud.json` to `.gitignore` to avoid committing credentials.

---

## Common Workflows

### Workflow 1: New Project Setup

```bash
# 1. Install DynamicCRUD
composer require dynamiccrud/dynamiccrud

# 2. Initialize configuration
php vendor/bin/dynamiccrud init

# 3. List existing tables
php vendor/bin/dynamiccrud list:tables

# 4. Generate metadata for tables
php vendor/bin/dynamiccrud generate:metadata users
php vendor/bin/dynamiccrud generate:metadata posts

# 5. Run generated SQL statements on database

# 6. Validate metadata
php vendor/bin/dynamiccrud validate:metadata users
```

### Workflow 2: Adding Metadata to Existing Tables

```bash
# 1. Generate metadata
php vendor/bin/dynamiccrud generate:metadata products

# 2. Copy SQL statement and customize if needed

# 3. Run SQL on database

# 4. Validate
php vendor/bin/dynamiccrud validate:metadata products

# 5. Clear cache
php vendor/bin/dynamiccrud clear:cache
```

### Workflow 3: Debugging

```bash
# 1. List all tables to see overview
php vendor/bin/dynamiccrud list:tables

# 2. Validate specific table
php vendor/bin/dynamiccrud validate:metadata problematic_table

# 3. Clear cache
php vendor/bin/dynamiccrud clear:cache

# 4. Regenerate metadata if needed
php vendor/bin/dynamiccrud generate:metadata problematic_table
```

---

## Tips & Best Practices

### 1. Version Control

Add to `.gitignore`:
```
dynamiccrud.json
cache/
```

### 2. Environment-Specific Config

Use different config files per environment:
```bash
# Development
cp dynamiccrud.json.example dynamiccrud.json

# Production
# Use environment variables or secure config management
```

### 3. Metadata Customization

After generating metadata, customize before applying:
```bash
# 1. Generate
php vendor/bin/dynamiccrud generate:metadata users

# 2. Copy SQL output

# 3. Edit JSON to add custom fields:
{
    "display_name": "User Management",
    "icon": "👥",
    "color": "#667eea",
    "permissions": {
        "create": ["admin"],
        "read": ["*"],
        "update": ["admin", "owner"],
        "delete": ["admin"]
    }
}

# 4. Run customized SQL
```

### 4. Batch Operations

Generate metadata for multiple tables:
```bash
for table in users posts comments; do
    php vendor/bin/dynamiccrud generate:metadata $table
done
```

### 5. CI/CD Integration

```yaml
# .github/workflows/deploy.yml
- name: Validate Metadata
  run: |
    php vendor/bin/dynamiccrud list:tables
    php vendor/bin/dynamiccrud validate:metadata users
    php vendor/bin/dynamiccrud validate:metadata posts
```

---

## Troubleshooting

### Error: "Configuration file not found"

**Problem:** `dynamiccrud.json` doesn't exist

**Solution:**
```bash
php vendor/bin/dynamiccrud init
```

### Error: "Invalid configuration file"

**Problem:** Malformed JSON in `dynamiccrud.json`

**Solution:** Validate JSON syntax or regenerate:
```bash
rm dynamiccrud.json
php vendor/bin/dynamiccrud init
```

### Error: "Table not found"

**Problem:** Table doesn't exist in database

**Solution:** Check table name and database connection:
```bash
php vendor/bin/dynamiccrud list:tables
```

### Cache Not Clearing

**Problem:** Cache directory doesn't exist

**Solution:** Create cache directory:
```bash
mkdir -p cache/templates
```

---

## Advanced Usage

### Custom Config Location

```php
// In your PHP code
$config = json_decode(file_get_contents('/path/to/custom-config.json'), true);
$pdo = new PDO(
    "{$config['driver']}:host={$config['host']};dbname={$config['database']}",
    $config['username'],
    $config['password']
);
```

### Programmatic Metadata Generation

```php
use DynamicCRUD\SchemaAnalyzer;

$pdo = new PDO(/* ... */);
$analyzer = new SchemaAnalyzer($pdo);
$schema = $analyzer->getTableSchema('users');

// Generate custom metadata
$metadata = [
    'display_name' => 'Users',
    'icon' => '👥',
    // ... custom configuration
];
```

---

## Related Documentation

- [Table Metadata Guide](TABLE_METADATA.md) - Complete metadata options
- [RBAC & Authentication Guide](RBAC.md) - Permissions and auth
- [Customization Guide](CUSTOMIZATION.md) - Field metadata options

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
