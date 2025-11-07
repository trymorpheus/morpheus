# WordPress Migration Example

**Status:** In Progress (Step 1/6 Complete)  
**Version:** 4.0.0

## 📋 Overview

This example demonstrates how to migrate a WordPress site to DynamicCRUD using the WordPress Migration Tool.

## ✅ Completed Features

### Step 1: WXR Parser ✅

Parse WordPress eXtended RSS (WXR) export files.

**Features:**
- Parse site information
- Extract categories
- Extract tags
- Extract posts
- Extract authors
- Handle WordPress namespaces

**Test:**
```bash
php test-parser.php
```

### Step 2: Content Mapper ✅

Map WordPress structure to DynamicCRUD format.

**Features:**
- Map categories (name, slug)
- Map tags (name, slug)
- Map posts with all fields
- Convert post status (publish → published, draft → draft)
- Convert dates to MySQL format
- Remove WordPress shortcodes ([caption], [gallery])
- Convert WordPress image classes to inline styles
- Extract image URLs from content
- Replace image URLs after download

**Test:**
```bash
php test-mapper.php
```

### Step 3: Media Downloader ✅

Download media files from WordPress URLs.

**Features:**
- Download images from URLs
- Sanitize filenames
- Handle duplicates with unique suffixes
- Batch download support
- Download caching (avoid re-downloading)
- Statistics tracking

**Test:**
```bash
php test-downloader.php
```

### Step 4: Main Migrator ✅

Orchestrate complete WordPress migration.

**Features:**
- Full migration orchestration
- Import categories with slug mapping
- Import tags with slug mapping
- Import posts with content conversion
- Download and replace media URLs (optional)
- Generate URL mappings (old → new)
- Track statistics (categories, tags, posts, media, errors)
- Transaction support (rollback on error)
- Generate .htaccess redirects
- Generate nginx redirects

**Test:**
```bash
php setup.php    # Create tables
php migrate.php  # Run migration
```

## 🚧 In Progress

### Step 5: CLI Command (Next)
- Command-line interface
- Progress reporting
- Options (--prefix, --download-media, --dry-run)
- Error handling

### Step 4: Main Migrator (In Progress)
- Orchestrate full migration
- Import categories, tags, posts
- Download and link media
- Generate URL mapping
- Track statistics

### Step 5: CLI Command
- Command-line interface
- Progress reporting
- Error handling

### Step 6: Full Example
- Complete migration script
- Documentation
- Troubleshooting guide

## 📁 Files

- `sample.xml` - Sample WordPress export (3 posts, 2 categories, 2 tags)
- `setup.php` - Create database tables with wp_ prefix
- `migrate.php` - Complete migration script
- `test-parser.php` - Test script for WXR parser
- `test-mapper.php` - Test script for Content Mapper
- `test-downloader.php` - Test script for Media Downloader
- `redirects.htaccess` - Generated redirect rules
- `uploads/` - Directory for downloaded media
- `README.md` - This file

## 🎯 Sample Data

The `sample.xml` file contains:
- **Site:** My WordPress Blog
- **Categories:** Technology, Programming
- **Tags:** PHP, MySQL
- **Authors:** Admin User
- **Posts:** 
  - Getting Started with PHP (published)
  - MySQL Database Basics (published)
  - Advanced PHP Techniques (draft)

## 🚀 Quick Start

```bash
# 1. Create database tables
php setup.php

# 2. Run migration
php migrate.php

# Expected output:
# ✅ Migration completed successfully!
# 
# 📊 Statistics:
#   Categories imported: 2
#   Tags imported: 2
#   Posts imported: 3
#   Media downloaded: 0
#   Errors: 0
#   Duration: 0.02s
# 
# 🔗 URL Mappings:
#   https://example.com/2024/01/getting-started-with-php/
#   → /blog/getting-started-with-php
#   ...
# 
# 📄 Generating redirects...
#   ✅ Saved to: redirects.htaccess
```

## 🧪 Individual Tests

```bash
# Test parser only
php test-parser.php

# Test mapper only
php test-mapper.php

# Test downloader only
php test-downloader.php
```

## 📚 Documentation

See [WordPress Migration Plan](../../local_docs/WORDPRESS_MIGRATION_PLAN.md) for complete implementation details.

## 🔗 Related

- [Content Types Guide](../../docs/CONTENT_TYPES.md)
- [Blog CMS Example](../24-blog-cms/)
- [v4.0 Progress](../../local_docs/V4.0_PROGRESS.md)

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
