# Upgrading to v2.0

This guide helps you upgrade from Morpheus v1.x to v2.0.

## 🎉 Good News: Zero Breaking Changes!

v2.0 is **100% backward compatible** with v1.x. All your existing code will continue to work without any modifications.

## ✨ What's New in v2.0

v2.0 introduces the **Table Metadata System** - a revolutionary way to configure CRUD behavior via JSON in database table comments.

### New Features

1. **UI/UX Customization** - Custom list views, colors, icons
2. **Dynamic Forms** - Tabbed forms and fieldsets
3. **Automatic Behaviors** - Auto-timestamps and auto-slugs
4. **Search & Filters** - Full-text search and advanced filters

All features are **optional** and **additive** - they don't change existing behavior.

---

## 📋 Upgrade Steps

### Step 1: Update Dependencies

```bash
composer update trymorpheus/morpheus
```

### Step 2: Clear Cache

```bash
php examples/clear_cache.php
```

Or manually:
```bash
rm -rf cache/*.cache
rm -rf cache/templates/*
```

### Step 3: Test Existing Code

Run your existing code - everything should work exactly as before:

```php
$crud = new Morpheus($pdo, 'users');
echo $crud->renderForm();
$crud->handleSubmission();
// ✅ Works exactly as in v1.x
```

---

## 🚀 Adopting v2.0 Features (Optional)

### Option 1: Start Small

Add basic metadata to one table:

```sql
ALTER TABLE users COMMENT = '{
    "display_name": "User Management",
    "icon": "👥"
}';
```

Clear cache and test:
```bash
php examples/clear_cache.php
```

### Option 2: Add List Views

Enable search and custom columns:

```sql
ALTER TABLE users COMMENT = '{
    "display_name": "User Management",
    "icon": "👥",
    "list_view": {
        "columns": ["id", "name", "email", "created_at"],
        "searchable": ["name", "email"],
        "per_page": 25
    }
}';
```

Use the new list renderer:
```php
$crud = new Morpheus($pdo, 'users');
echo $crud->renderList(); // NEW in v2.0
```

### Option 3: Add Automatic Behaviors

Enable auto-timestamps and slugs:

```sql
ALTER TABLE posts COMMENT = '{
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

Now timestamps and slugs are automatic:
```php
$crud = new Morpheus($pdo, 'posts');
$result = $crud->handleSubmission();
// Slug and timestamps set automatically!
```

### Option 4: Add Tabbed Forms

Organize complex forms with tabs:

```sql
ALTER TABLE contacts COMMENT = '{
    "form": {
        "layout": "tabs",
        "tabs": [
            {
                "name": "basic",
                "label": "Basic Info",
                "fields": ["name", "email"]
            },
            {
                "name": "details",
                "label": "Details",
                "fields": ["phone", "website", "message"]
            }
        ]
    }
}';
```

Forms now render with tabs automatically:
```php
$crud = new Morpheus($pdo, 'contacts');
echo $crud->renderForm(); // Tabbed layout!
```

### Option 5: Add Search & Filters

Enable advanced search and filtering:

```sql
ALTER TABLE posts COMMENT = '{
    "list_view": {
        "searchable": ["title", "content"]
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
    ]
}';
```

Lists now include search and filters:
```php
$crud = new Morpheus($pdo, 'posts');
echo $crud->renderList(); // Search + filters!
```

---

## 📊 Migration Checklist

Use this checklist to track your migration progress:

### Pre-Migration
- [ ] Backup database
- [ ] Update composer dependencies
- [ ] Clear cache
- [ ] Test existing code (should work unchanged)

### Per-Table Migration (Optional)
- [ ] Add basic metadata (display_name, icon)
- [ ] Configure list view (columns, searchable, per_page)
- [ ] Add filters (if needed)
- [ ] Configure automatic behaviors (timestamps, sluggable)
- [ ] Organize forms with tabs (if complex)
- [ ] Test each feature individually
- [ ] Clear cache after changes

### Post-Migration
- [ ] Update documentation
- [ ] Train team on new features
- [ ] Monitor performance
- [ ] Gather feedback

---

## 🔧 Troubleshooting

### Cache Issues

**Problem:** Changes to table metadata not appearing

**Solution:**
```bash
php examples/clear_cache.php
```

### JSON Syntax Errors

**Problem:** Invalid JSON in table comment

**Solution:** Validate JSON before adding:
```bash
echo '{"display_name": "Users"}' | python -m json.tool
```

Or use online validator: https://jsonlint.com/

### Metadata Not Loading

**Problem:** TableMetadata returns empty

**Solution:** Check table comment exists:
```sql
SELECT table_name, table_comment 
FROM information_schema.tables 
WHERE table_schema = 'your_database' 
AND table_name = 'your_table';
```

### Slug Not Generating

**Problem:** Slug field stays empty

**Solution:** Ensure:
1. Slug field exists in table
2. Slug field is empty (not readonly in form)
3. Source field has value
4. Cache is cleared

### Search Not Working

**Problem:** Search returns no results

**Solution:** Ensure:
1. `searchable` fields are text fields (not int/date)
2. Search term matches content
3. Cache is cleared

---

## 📚 Learning Resources

### Documentation
- [Table Metadata Guide](TABLE_METADATA.md) - Complete reference
- [Table Metadata Roadmap](TABLE_METADATA_IDEAS.md) - Future features
- [Examples](../examples/06-table-metadata/) - Working examples

### Examples
- `06-table-metadata/ui-customization.php` - List views
- `06-table-metadata/dynamic-forms.php` - Tabbed forms
- `06-table-metadata/automatic-behaviors.php` - Auto-slug/timestamps
- `06-table-metadata/search-filters.php` - Search and filters

---

## 🎯 Best Practices

### 1. Migrate Gradually
Don't migrate all tables at once. Start with:
1. Most-used tables
2. Tables with complex forms (benefit from tabs)
3. Tables needing search (benefit from filters)

### 2. Test in Development First
Always test metadata changes in development before production.

### 3. Version Control Your Schema
Keep table metadata in version control:
```sql
-- migrations/002_add_users_metadata.sql
ALTER TABLE users COMMENT = '{...}';
```

### 4. Document Your Metadata
Add comments explaining why certain metadata was chosen:
```sql
-- Enable search on name/email for quick user lookup
ALTER TABLE users COMMENT = '{
    "list_view": {"searchable": ["name", "email"]}
}';
```

### 5. Monitor Performance
After adding search/filters, monitor query performance:
```sql
EXPLAIN SELECT * FROM posts WHERE title LIKE '%term%';
```

Consider adding indexes:
```sql
CREATE INDEX idx_posts_title ON posts(title);
```

---

## 🆘 Getting Help

If you encounter issues:

1. **Check Documentation**: [TABLE_METADATA.md](TABLE_METADATA.md)
2. **Review Examples**: [examples/06-table-metadata/](../examples/06-table-metadata/)
3. **Clear Cache**: `php examples/clear_cache.php`
4. **Check Logs**: Look for PHP errors
5. **Open Issue**: [GitHub Issues](https://github.com/trymorpheus/morpheus/issues)

---

## 🎉 Success Stories

Share your migration experience! Open a discussion on GitHub to help others.

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
