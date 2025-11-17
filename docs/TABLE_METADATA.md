# Table Metadata Guide (v2.0)

**NEW in v2.0** - Configure table-level behavior via JSON in database table comments.

## 📋 Overview

Table metadata allows you to control table-level features without writing code. Simply add JSON to your table's `COMMENT` field and DynamicCRUD automatically applies the configuration.

**Benefits:**
- ✅ Zero configuration in PHP code
- ✅ Portable (metadata travels with database schema)
- ✅ Versionable (track changes in version control)
- ✅ DRY (Don't Repeat Yourself)
- ✅ Database-first approach

---

## 🎨 UI/UX Customization

Control how tables are displayed in lists and forms.

### Configuration

```sql
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(255)
) COMMENT = '{
    "display_name": "User Management",
    "icon": "👥",
    "description": "Complete user administration",
    "color": "#667eea",
    "list_view": {
        "columns": ["id", "name", "email", "created_at"],
        "default_sort": "created_at DESC",
        "per_page": 25,
        "searchable": ["name", "email"],
        "actions": ["edit", "delete", "view"],
        "card_view": false
    }
}';
```

### Options

| Option | Type | Description | Example |
|--------|------|-------------|---------|
| `display_name` | string | Friendly table name | `"User Management"` |
| `icon` | string | Emoji or icon | `"👥"` |
| `description` | string | Table description | `"Manage system users"` |
| `color` | string | Accent color (hex) | `"#667eea"` |
| `list_view.columns` | array | Columns to show in list | `["id", "name", "email"]` |
| `list_view.default_sort` | string | Default sorting | `"created_at DESC"` |
| `list_view.per_page` | int | Records per page | `25` |
| `list_view.searchable` | array | Searchable fields | `["name", "email"]` |
| `list_view.actions` | array | Available actions | `["edit", "delete"]` |
| `list_view.card_view` | bool | Use card layout | `false` |

### Usage

```php
$crud = new Morpheus($pdo, 'users');
echo $crud->renderList(); // Automatically styled with metadata
```

---

## 📑 Dynamic Forms

Organize forms with tabs and fieldsets.

### Tabbed Forms

```sql
CREATE TABLE contacts (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(20),
    website VARCHAR(255),
    message TEXT
) COMMENT = '{
    "form": {
        "layout": "tabs",
        "tabs": [
            {
                "name": "basic",
                "label": "Basic Info",
                "fields": ["name", "email"]
            },
            {
                "name": "contact",
                "label": "Contact Details",
                "fields": ["phone", "website"]
            },
            {
                "name": "message",
                "label": "Message",
                "fields": ["message"]
            }
        ]
    }
}';
```

### Options

| Option | Type | Description | Example |
|--------|------|-------------|---------|
| `form.layout` | string | Form layout type | `"tabs"` or `"standard"` |
| `form.tabs` | array | Tab definitions | See example above |
| `form.tabs[].name` | string | Tab identifier | `"basic"` |
| `form.tabs[].label` | string | Tab display label | `"Basic Info"` |
| `form.tabs[].fields` | array | Fields in this tab | `["name", "email"]` |
| `form.columns` | int | Form columns (future) | `2` |

### Usage

```php
$crud = new Morpheus($pdo, 'contacts');
echo $crud->renderForm(); // Automatically renders tabs
```

---

## 🤖 Automatic Behaviors

Automate common patterns like timestamps and slugs.

### Timestamps

Automatically set `created_at` and `updated_at` fields.

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
) COMMENT = '{
    "behaviors": {
        "timestamps": {
            "created_at": "created_at",
            "updated_at": "updated_at"
        }
    }
}';
```

**How it works:**
- `created_at` is set only on INSERT
- `updated_at` is set on both INSERT and UPDATE
- No code needed - completely automatic

### Sluggable

Automatically generate URL-friendly slugs from a source field.

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    slug VARCHAR(255) UNIQUE
) COMMENT = '{
    "behaviors": {
        "sluggable": {
            "source": "title",
            "target": "slug",
            "unique": true,
            "separator": "-",
            "lowercase": true
        }
    }
}';
```

**Options:**

| Option | Type | Description | Default |
|--------|------|-------------|---------|
| `source` | string | Source field | `"title"` |
| `target` | string | Target slug field | `"slug"` |
| `unique` | bool | Ensure uniqueness | `true` |
| `separator` | string | Word separator | `"-"` |
| `lowercase` | bool | Convert to lowercase | `true` |

**How it works:**
- Slug is generated from `source` field
- Special characters are removed
- Spaces become separators
- If `unique: true`, adds numeric suffix if slug exists (e.g., `my-post-1`, `my-post-2`)
- Only generates if `target` field is empty

**Example:**
- Title: `"My Amazing Blog Post"`
- Slug: `"my-amazing-blog-post"`
- If exists: `"my-amazing-blog-post-1"`

### Usage

```php
$crud = new Morpheus($pdo, 'posts');
$result = $crud->handleSubmission();
// Slug and timestamps are automatically set!
```

---

## 🔍 Search & Filters

Advanced search and filtering with zero configuration.

### Full-Text Search

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    content TEXT
) COMMENT = '{
    "list_view": {
        "searchable": ["title", "content"]
    }
}';
```

**How it works:**
- Searches across all specified fields using `LIKE %term%`
- Combines fields with `OR` logic
- Preserves filters when searching

### Filters

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255),
    status ENUM('draft', 'published'),
    created_at TIMESTAMP
) COMMENT = '{
    "filters": [
        {
            "field": "status",
            "type": "select",
            "label": "Estado",
            "options": ["draft", "published"]
        },
        {
            "field": "created_at",
            "type": "daterange",
            "label": "Fecha de Creación"
        }
    ]
}';
```

**Filter Types:**

#### Select Filter
```json
{
    "field": "status",
    "type": "select",
    "label": "Status",
    "options": ["draft", "published", "archived"]
}
```

#### Date Range Filter
```json
{
    "field": "created_at",
    "type": "daterange",
    "label": "Creation Date"
}
```

**How it works:**
- Filters combine with `AND` logic
- Search combines with filters
- All parameters preserved during pagination
- Clean URLs with query strings

### Usage

```php
$crud = new Morpheus($pdo, 'posts');
echo $crud->renderList();
// Automatically includes search box and filters
```

**URL Examples:**
- Search: `?search=docker`
- Filter: `?status=published`
- Date range: `?created_at_from=2024-01-01&created_at_to=2024-12-31`
- Combined: `?search=php&status=published&page=2`

---

## 📊 Complete Example

Here's a real-world example combining all features:

```sql
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    content TEXT,
    status ENUM('draft', 'published') DEFAULT 'draft',
    category_id INT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) COMMENT = '{
    "display_name": "Blog Posts",
    "icon": "📝",
    "color": "#28a745",
    "description": "Manage blog posts and articles",
    "list_view": {
        "columns": ["id", "title", "status", "created_at"],
        "default_sort": "created_at DESC",
        "per_page": 20,
        "searchable": ["title", "content"],
        "actions": ["edit", "delete"]
    },
    "filters": [
        {
            "field": "status",
            "type": "select",
            "label": "Status",
            "options": ["draft", "published"]
        },
        {
            "field": "created_at",
            "type": "daterange",
            "label": "Creation Date"
        }
    ],
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
}';
```

**PHP Usage:**

```php
$crud = new Morpheus($pdo, 'posts');

// Render list with search, filters, pagination
echo $crud->renderList();

// Render form with automatic behaviors
echo $crud->renderForm($_GET['id'] ?? null);

// Handle submission (slug and timestamps automatic)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    if ($result['success']) {
        echo "Saved! Slug: " . $result['slug'];
    }
}
```

---

## 🎯 Best Practices

### 1. Keep Metadata in Database
Store configuration in table comments, not PHP files. This makes it:
- Portable across environments
- Versionable with schema migrations
- Accessible to all applications using the database

### 2. Use Meaningful Names
```json
{
    "display_name": "User Management",  // ✅ Good
    "display_name": "users"             // ❌ Bad
}
```

### 3. Configure Searchable Fields Wisely
Only make text fields searchable:
```json
{
    "searchable": ["title", "content", "description"]  // ✅ Good
    "searchable": ["id", "created_at", "price"]        // ❌ Bad
}
```

### 4. Limit List Columns
Show only essential columns in lists:
```json
{
    "columns": ["id", "name", "email", "status"]  // ✅ Good (4 columns)
    "columns": ["id", "name", "email", "phone", "address", "city", "state", "zip", "country"]  // ❌ Bad (too many)
}
```

### 5. Use Appropriate Per-Page Values
```json
{
    "per_page": 25   // ✅ Good for most tables
    "per_page": 100  // ⚠️ OK for small records
    "per_page": 5    // ⚠️ Too few (too much pagination)
}
```

---

## 🔧 API Reference

### TableMetadata Class

```php
$metadata = new TableMetadata($pdo, 'posts');

// UI/UX
$metadata->getDisplayName();      // string
$metadata->getIcon();              // ?string
$metadata->getColor();             // ?string
$metadata->getDescription();       // ?string
$metadata->getListColumns();       // array
$metadata->getDefaultSort();       // string
$metadata->getPerPage();           // int
$metadata->getSearchableFields();  // array
$metadata->getActions();           // array
$metadata->hasCardView();          // bool

// Forms
$metadata->getFormLayout();        // string ('tabs' or 'standard')
$metadata->getTabs();              // array
$metadata->getFieldsets();         // array
$metadata->getFormColumns();       // int

// Behaviors
$metadata->hasTimestamps();        // bool
$metadata->getTimestampFields();   // array
$metadata->isSluggable();          // bool
$metadata->getSluggableConfig();   // array
$metadata->isSortable();           // bool
$metadata->getSortableConfig();    // array

// Search & Filters
$metadata->getSearchFields();      // array
$metadata->getFilters();           // array
```

---

## 🚀 Migration from v1.x

If you're upgrading from v1.x, table metadata is **optional**. Tables without metadata work exactly as before.

**To add metadata:**

1. Add JSON to table comment:
```sql
ALTER TABLE users COMMENT = '{"display_name": "Users", "icon": "👥"}';
```

2. Clear cache:
```bash
php examples/clear_cache.php
```

3. Use new features:
```php
$crud->renderList();  // Now uses metadata
```

---

## 📚 See Also

- [Table Metadata Ideas](TABLE_METADATA_IDEAS.md) - Full roadmap and future features
- [Examples](../examples/06-table-metadata/) - Working examples
- [Main README](../README.md) - Complete documentation

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
