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

## 🚧 In Progress

### Step 4: Main Migrator (Next)
- Orchestrate full migration
- Import categories, tags, posts
- Download and link media
- Generate URL mapping
- Track statistics

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
- `test-parser.php` - Test script for WXR parser
- `test-mapper.php` - Test script for Content Mapper
- `test-downloader.php` - Test script for Media Downloader
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

## 🚀 Quick Test

```bash
# Test the parser
php test-parser.php

# Expected output:
# 🔍 Testing WordPress XML Parser
# 
# Site Information:
#   Title: My WordPress Blog
#   Link: https://example.com
#   Language: en-US
# 
# Categories: 2
#   - Technology (slug: technology)
#   - Programming (slug: programming)
# 
# Tags: 2
#   - PHP (slug: php)
#   - MySQL (slug: mysql)
# 
# Posts: 3
#   - Getting Started with PHP (publish)
#   - MySQL Database Basics (publish)
#   - Advanced PHP Techniques (draft)
# 
# ✅ Parser test completed!
```

## 📚 Documentation

See [WordPress Migration Plan](../../local_docs/WORDPRESS_MIGRATION_PLAN.md) for complete implementation details.

## 🔗 Related

- [Content Types Guide](../../docs/CONTENT_TYPES.md)
- [Blog CMS Example](../24-blog-cms/)
- [v4.0 Progress](../../local_docs/V4.0_PROGRESS.md)

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
