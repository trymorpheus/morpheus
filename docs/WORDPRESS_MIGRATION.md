# WordPress Migration Guide

**Version:** 4.0.0  
**Status:** Production Ready

## 📋 Table of Contents

- [Overview](#overview)
- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Step-by-Step Guide](#step-by-step-guide)
- [CLI Reference](#cli-reference)
- [PHP API](#php-api)
- [What Gets Migrated](#what-gets-migrated)
- [Post-Migration](#post-migration)
- [Troubleshooting](#troubleshooting)
- [Best Practices](#best-practices)

---

## Overview

The WordPress Migration Tool allows you to migrate your WordPress site to DynamicCRUD in minutes. It parses WordPress eXtended RSS (WXR) export files and imports all content into DynamicCRUD's blog structure.

**Key Benefits:**
- ✅ **Fast** - Migrate 1000+ posts in <5 minutes
- ✅ **Complete** - Posts, categories, tags, and relationships
- ✅ **Safe** - Transaction-based with rollback on error
- ✅ **SEO-Friendly** - Automatic URL mapping and redirects
- ✅ **Zero Downtime** - Migrate without affecting live site

---

## Prerequisites

### 1. Export WordPress Site

In your WordPress admin:
1. Go to **Tools → Export**
2. Select **All content**
3. Click **Download Export File**
4. Save the `.xml` file

### 2. Install DynamicCRUD

```bash
composer require trymorpheus/morpheus
```

### 3. Setup Database

Create database tables for the blog:

```bash
# Using example setup
php examples/26-wordpress-migration/setup.php

# Or manually create tables
# See examples/26-wordpress-migration/setup.php for SQL
```

---

## Quick Start

### Using CLI (Recommended)

```bash
# Basic migration
php bin/morpheus migrate:wordpress export.xml

# With options
php bin/morpheus migrate:wordpress export.xml \
  --prefix=wp_ \
  --generate-redirects \
  --verbose
```

### Using PHP

```php
use Morpheus\Migration\WordPressMigrator;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
$migrator = new WordPressMigrator($pdo, 'wp_', __DIR__ . '/uploads');

$result = $migrator->migrate('export.xml', [
    'download_media' => true
]);

if ($result['success']) {
    echo "Migrated {$result['stats']['posts']} posts!\n";
}
```

---

## Step-by-Step Guide

### Step 1: Export WordPress Content

1. Login to WordPress admin
2. Navigate to **Tools → Export**
3. Select **All content** (or specific content types)
4. Click **Download Export File**
5. Save as `wordpress-export.xml`

**What's Included:**
- Posts and pages
- Categories and tags
- Authors
- Comments (structure only)
- Custom fields
- Featured images (URLs)

### Step 2: Prepare DynamicCRUD

Create the database tables:

```bash
cd examples/26-wordpress-migration
php setup.php
```

This creates:
- `{prefix}categories` - Blog categories
- `{prefix}tags` - Blog tags
- `{prefix}posts` - Blog posts
- `{prefix}post_tags` - Post-tag relationships

### Step 3: Run Migration

```bash
php bin/morpheus migrate:wordpress wordpress-export.xml \
  --prefix=wp_ \
  --generate-redirects \
  --verbose
```

**Expected Output:**
```
🔄 WordPress to DynamicCRUD Migration
==================================================

📋 Configuration:
  WXR File: wordpress-export.xml
  Table Prefix: wp_
  Upload Dir: ./uploads
  Download Media: Yes

🚀 Starting migration...

✅ Migration completed successfully!

📊 Statistics:
  Categories imported: 10
  Tags imported: 25
  Posts imported: 150
  Media downloaded: 75
  Duration: 2.5s

🔗 URL Mappings: 150 URLs mapped
📄 Redirects saved to: redirects.htaccess
```

### Step 4: Verify Migration

Check the imported data:

```bash
# Count records
php -r "
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'pass');
echo 'Posts: ' . $pdo->query('SELECT COUNT(*) FROM wp_posts')->fetchColumn() . PHP_EOL;
echo 'Categories: ' . $pdo->query('SELECT COUNT(*) FROM wp_categories')->fetchColumn() . PHP_EOL;
echo 'Tags: ' . $pdo->query('SELECT COUNT(*) FROM wp_tags')->fetchColumn() . PHP_EOL;
"
```

### Step 5: Setup Redirects

Copy the generated redirect file to your web root:

```bash
# For Apache
cp redirects.htaccess /var/www/html/.htaccess

# For Nginx
# Add rules from redirects.nginx to your nginx config
```

---

## CLI Reference

### Command

```bash
php bin/morpheus migrate:wordpress <wxr-file> [options]
```

### Options

| Option | Description | Default |
|--------|-------------|---------|
| `--prefix=PREFIX` | Table prefix | none |
| `--host=HOST` | Database host | localhost |
| `--database=DB` | Database name | test |
| `--username=USER` | Database username | root |
| `--password=PASS` | Database password | rootpassword |
| `--upload-dir=DIR` | Upload directory | ./uploads |
| `--no-media` | Skip media download | false |
| `--dry-run` | Preview without changes | false |
| `--generate-redirects` | Generate redirect rules | false |
| `--redirect-format=FMT` | htaccess or nginx | htaccess |
| `--redirect-output=OUT` | Output file | redirects.FORMAT |
| `--verbose` | Show detailed output | false |
| `--help` | Show help message | - |

### Examples

```bash
# Basic migration
php bin/morpheus migrate:wordpress export.xml

# With table prefix
php bin/morpheus migrate:wordpress export.xml --prefix=blog_

# Skip media download
php bin/morpheus migrate:wordpress export.xml --no-media

# Generate nginx redirects
php bin/morpheus migrate:wordpress export.xml \
  --generate-redirects \
  --redirect-format=nginx

# Dry run (preview)
php bin/morpheus migrate:wordpress export.xml --dry-run

# Custom database
php bin/morpheus migrate:wordpress export.xml \
  --host=db.example.com \
  --database=production \
  --username=dbuser \
  --password=secret
```

---

## PHP API

### Basic Usage

```php
use Morpheus\Migration\WordPressMigrator;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
$migrator = new WordPressMigrator($pdo, 'wp_', __DIR__ . '/uploads');

$result = $migrator->migrate('export.xml');

if ($result['success']) {
    print_r($result['stats']);
} else {
    echo "Error: {$result['error']}\n";
}
```

### Advanced Usage

```php
// With options
$result = $migrator->migrate('export.xml', [
    'download_media' => true
]);

// Get URL mappings
$urlMap = $migrator->getUrlMap();
foreach ($urlMap as $oldUrl => $newUrl) {
    echo "{$oldUrl} → {$newUrl}\n";
}

// Generate redirects
$htaccess = $migrator->generateRedirects('htaccess');
file_put_contents('.htaccess', $htaccess);

$nginx = $migrator->generateRedirects('nginx');
file_put_contents('redirects.conf', $nginx);
```

### Individual Components

```php
use Morpheus\Migration\WXRParser;
use Morpheus\Migration\ContentMapper;
use Morpheus\Migration\MediaDownloader;

// Parse WXR file
$parser = new WXRParser();
$data = $parser->parse('export.xml');

// Map content
$mapper = new ContentMapper();
$post = $mapper->mapPost($data['posts'][0]);

// Download media
$downloader = new MediaDownloader(__DIR__ . '/uploads');
$filename = $downloader->download('https://example.com/image.jpg');
```

---

## What Gets Migrated

### ✅ Migrated

- **Posts**
  - Title, slug, content, excerpt
  - Status (publish → published, draft → draft)
  - Published date
  - Category assignment
  - Tag assignments
  
- **Categories**
  - Name and slug
  - Description (empty)
  
- **Tags**
  - Name and slug
  
- **Relationships**
  - Post-category (one-to-many)
  - Post-tags (many-to-many)
  
- **URLs**
  - Old → new URL mapping
  - Redirect rules generation

### ⚠️ Partially Migrated

- **Media**
  - Image URLs extracted from content
  - Optional download to local storage
  - URLs replaced in content
  - Featured images (metadata only)

### ❌ Not Migrated

- **Comments** - Structure exists but not imported
- **Authors** - Parsed but not imported (single author assumed)
- **Custom Post Types** - Only 'post' and 'page' types
- **Plugins Data** - WordPress-specific plugin data
- **Theme Settings** - WordPress theme configurations
- **Widgets** - Sidebar widgets
- **Menus** - Navigation menus

---

## Post-Migration

### 1. Verify Content

Check that all content was imported:

```bash
# View posts
php -r "
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'pass');
$stmt = $pdo->query('SELECT title, status FROM wp_posts LIMIT 5');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo \"{$row['title']} - {$row['status']}\n\";
}
"
```

### 2. Setup Redirects

**Apache (.htaccess):**
```apache
RewriteEngine On
RewriteRule ^2024/01/my-post/$ /blog/my-post [R=301,L]
```

**Nginx:**
```nginx
rewrite ^/2024/01/my-post/$ /blog/my-post permanent;
```

### 3. Configure Theme

Apply a theme to your blog:

```php
use Morpheus\Theme\ThemeManager;
use Morpheus\Theme\Themes\ModernTheme;

$themeManager = new ThemeManager($pdo, __DIR__ . '/themes');
$themeManager->register('modern', new ModernTheme(__DIR__ . '/themes'));
$themeManager->activate('modern');
```

### 4. Test Site

Visit your new blog:
- Homepage: `http://localhost/blog`
- Single post: `http://localhost/blog/my-post`
- Category: `http://localhost/blog/category/technology`
- Admin: `http://localhost/admin.php`

---

## Troubleshooting

### Large Files

**Problem:** WXR file too large (>100MB)

**Solution:** Split the export or increase PHP memory:
```bash
php -d memory_limit=512M bin/morpheus migrate:wordpress large-export.xml
```

### Timeout

**Problem:** Migration times out

**Solution:** Increase PHP timeout:
```bash
php -d max_execution_time=300 bin/morpheus migrate:wordpress export.xml
```

### Media Download Fails

**Problem:** Images fail to download

**Solution:** Skip media download and handle separately:
```bash
php bin/morpheus migrate:wordpress export.xml --no-media
```

### Duplicate Slugs

**Problem:** Posts with duplicate slugs

**Solution:** DynamicCRUD will fail on duplicate slugs. Clean up WordPress before export or modify slugs manually.

### Missing Tables

**Problem:** Table not found error

**Solution:** Run setup script first:
```bash
php examples/26-wordpress-migration/setup.php
```

### Character Encoding

**Problem:** Special characters display incorrectly

**Solution:** Ensure database uses UTF-8:
```sql
ALTER DATABASE test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## Best Practices

### Before Migration

1. **Backup WordPress** - Full database and files backup
2. **Clean Content** - Remove spam, drafts, and unused content
3. **Test Export** - Verify WXR file is valid XML
4. **Check Slugs** - Ensure no duplicate post slugs
5. **Document URLs** - Note important URLs for redirect testing

### During Migration

1. **Use Dry Run** - Test migration first with `--dry-run`
2. **Start Small** - Test with subset of content first
3. **Monitor Progress** - Use `--verbose` for detailed output
4. **Check Errors** - Review error messages carefully
5. **Verify Stats** - Confirm counts match expectations

### After Migration

1. **Verify Content** - Check random posts for accuracy
2. **Test Redirects** - Verify old URLs redirect correctly
3. **Check Media** - Ensure images display properly
4. **Test Search** - Verify search functionality works
5. **Monitor Performance** - Check page load times
6. **Update DNS** - Point domain to new site when ready

### Production Deployment

1. **Use Transactions** - Migration uses transactions automatically
2. **Test Rollback** - Verify rollback works on error
3. **Schedule Downtime** - Plan maintenance window
4. **Backup First** - Always backup before production migration
5. **Monitor Logs** - Watch for errors after deployment

---

## Performance

### Benchmarks

| Posts | Categories | Tags | Duration | Memory |
|-------|------------|------|----------|--------|
| 100 | 10 | 20 | 0.5s | 10MB |
| 500 | 20 | 50 | 1.5s | 25MB |
| 1000 | 30 | 100 | 3.0s | 50MB |
| 5000 | 50 | 200 | 15s | 150MB |

**Notes:**
- Tested on: PHP 8.2, MySQL 8.0, 16GB RAM
- Media download adds ~0.1s per image
- Large content (>10KB per post) increases time

### Optimization Tips

1. **Skip Media** - Use `--no-media` for faster migration
2. **Increase Memory** - Set `memory_limit=512M` for large sites
3. **Use SSD** - Faster disk I/O improves performance
4. **Batch Processing** - Split large exports into smaller files
5. **Disable Logging** - Reduce database writes during migration

---

## Related Documentation

- [Content Types Guide](CONTENT_TYPES.md)
- [Frontend Rendering Guide](FRONTEND_RENDERING.md)
- [Theme System Guide](THEMES.md)
- [SEO Guide](SEO.md)

---

## Support

**Issues:** https://github.com/trymorpheus/morpheus/issues  
**Discussions:** https://github.com/trymorpheus/morpheus/discussions  
**Email:** support@dynamiccrud.com

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
