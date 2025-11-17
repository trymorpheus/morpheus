# 06. Table Metadata Examples

**NEW in v2.0** - Table-level metadata for powerful zero-config features.

## What is Table Metadata?

Table metadata is JSON stored in database table comments that controls table-level behavior:
- UI customization (list views, colors, icons)
- Dynamic forms (tabs, fieldsets)
- Automatic behaviors (timestamps, slugs)
- Search and filters
- Permissions and security (coming soon)

## Examples in This Folder

### 1. UI Customization
**File:** `ui-customization.php`

Demonstrates:
- Custom display names and icons
- Configurable list views (columns, sorting, pagination)
- Searchable fields
- Card vs table layouts
- Custom colors per table

### 2. Dynamic Forms
**File:** `dynamic-forms.php`

Demonstrates:
- Tabbed form layouts
- Organized field groups
- Multi-step forms
- Collapsible fieldsets

### 3. Automatic Behaviors
**File:** `automatic-behaviors.php`

Demonstrates:
- Auto-timestamps (created_at, updated_at)
- Auto-slug generation from title
- Unique slug handling
- Zero-code automation

## How to Use

### 1. Define Metadata in SQL

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    slug VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
) COMMENT = '{
    "display_name": "Blog Posts",
    "icon": "📝",
    "color": "#28a745",
    "behaviors": {
        "timestamps": {
            "created_at": "created_at",
            "updated_at": "updated_at"
        },
        "sluggable": {
            "source": "title",
            "target": "slug",
            "unique": true
        }
    }
}';
```

### 2. Use in PHP (Zero Config!)

```php
$crud = new Morpheus($pdo, 'posts');
echo $crud->renderForm(); // Behaviors apply automatically!
```

## Available Metadata Options

### UI/UX Customization
```json
{
  "display_name": "User Management",
  "icon": "👥",
  "description": "Complete user administration",
  "color": "#667eea",
  "list_view": {
    "columns": ["id", "name", "email", "created_at"],
    "default_sort": "created_at DESC",
    "per_page": 25,
    "searchable": ["name", "email"],
    "actions": ["edit", "delete", "view"]
  }
}
```

### Dynamic Forms
```json
{
  "form": {
    "layout": "tabs",
    "tabs": [
      {
        "name": "basic",
        "label": "Basic Info",
        "fields": ["name", "email"]
      },
      {
        "name": "advanced",
        "label": "Advanced",
        "fields": ["settings", "preferences"]
      }
    ],
    "columns": 2
  }
}
```

### Automatic Behaviors
```json
{
  "behaviors": {
    "timestamps": {
      "created_at": "created_at",
      "updated_at": "updated_at"
    },
    "sluggable": {
      "source": "title",
      "target": "slug",
      "unique": true,
      "separator": "-",
      "lowercase": true
    }
  }
}
```

## Roadmap (Phase 1 - Quick Wins)

- ✅ UI/UX Customization
- ✅ Dynamic Forms (tabs)
- ✅ Automatic Behaviors (timestamps, slugs)
- 🔜 Search & Filters (advanced)
- 🔜 Permissions & Security
- 🔜 Validation Rules
- 🔜 API Generation

## Benefits

1. **Zero Configuration** - Define once in database, works everywhere
2. **Portable** - Metadata travels with database schema
3. **Versionable** - Track changes in version control
4. **DRY** - Don't repeat yourself across multiple files
5. **Database-First** - Schema is source of truth

## Learn More

- [Table Metadata Ideas](../../docs/TABLE_METADATA_IDEAS.md) - Full feature list
- [Main README](../../README.md) - Complete documentation
- [Examples Index](../index.html) - All examples
