# Example 27: One-Click Installer

Complete installation wizard for DynamicCRUD - WordPress-style setup in 60 seconds!

## 🎯 What This Example Demonstrates

- **System Requirements Check** - Validates PHP version, extensions, and permissions
- **Database Configuration** - Test connection before proceeding
- **Site Setup** - Configure site title, URL, language, and admin account
- **Content Type Selection** - Choose blog or empty site
- **Theme Selection** - Pick from 3 professional themes
- **Automated Installation** - Creates tables, admin user, and config file
- **Progress Tracking** - Visual feedback during installation

## 📋 Features

### Web Installer
- ✨ **Beautiful UI** - Modern, responsive design
- 🔄 **Step-by-step wizard** - 8 clear steps
- ✅ **Real-time validation** - Test database connection
- 📊 **Progress bar** - Visual installation progress
- 🎨 **Theme preview** - See themes before selecting
- 🌐 **Multi-language** - EN, ES, FR support

### CLI Installer
- 🖥️ **Interactive mode** - Guided prompts
- ⚡ **Non-interactive mode** - Automated with flags
- 🔍 **System check** - Validates requirements
- 📝 **Summary** - Shows configuration before install

## 🚀 Quick Start

### Web Installer

1. **Navigate to installer:**
   ```
   http://localhost/install/
   ```

2. **Follow the wizard:**
   - Welcome screen
   - System check (automatic)
   - Database configuration
   - Site information
   - Content type selection
   - Theme selection
   - Install
   - Success!

3. **Delete installer directory:**
   ```bash
   rm -rf install/
   ```

### CLI Installer

**Interactive Mode:**
```bash
php bin/morpheus install --interactive
```

**Non-Interactive Mode:**
```bash
php bin/morpheus install \
  --host=localhost \
  --database=dynamiccrud \
  --username=root \
  --password=secret \
  --site-title="My Blog" \
  --site-url="http://localhost" \
  --language=en \
  --admin-name="Admin" \
  --admin-email="admin@example.com" \
  --admin-password="admin123" \
  --content-type=blog \
  --theme=minimal
```

## 📁 Files Created

After installation, these files are created:

```
config.php                    # Database and site configuration
_dynamiccrud_config table     # Global configuration storage
_users table                  # User accounts
[content type tables]         # If blog selected: posts, categories, tags, etc.
```

## 🎨 Available Options

### Content Types
- **Blog** - WordPress-style blog (5 tables)
- **Empty Site** - Clean slate (0 tables)

### Themes
- **Minimal** - Clean and simple
- **Modern** - Contemporary and professional
- **Classic** - Traditional and elegant

### Languages
- **English** (en)
- **Spanish** (es)
- **French** (fr)

## 🔧 System Requirements

### Required
- PHP 8.0+
- PDO extension
- JSON extension
- FileInfo extension
- MBString extension

### Optional (Recommended)
- GD extension (image processing)
- cURL extension (external requests)
- ZIP extension (backups)

### Permissions
- `cache/` directory must be writable
- `examples/uploads/` directory must be writable

## 📊 Installation Steps

### Step 1: Welcome
- Introduction to DynamicCRUD
- Feature overview
- Prerequisites check

### Step 2: System Check
- PHP version validation
- Extension availability
- Directory permissions
- Auto-runs on page load

### Step 3: Database Configuration
- Driver selection (MySQL/PostgreSQL)
- Host, database, username, password
- Test connection button
- Real-time validation

### Step 4: Site Information
- Site title and URL
- Language selection
- Admin account creation
- Password strength validation

### Step 5: Content Type
- Visual selection cards
- Blog or empty site
- Table count preview

### Step 6: Theme
- Visual theme cards
- 3 professional themes
- Can be changed later

### Step 7: Install
- Configuration summary
- Progress bar
- Automated installation
- Error handling

### Step 8: Success
- Installation confirmation
- Next steps guide
- Links to site and admin
- Security reminder

## 🎯 What Gets Installed

### Core Tables
```sql
_dynamiccrud_config  -- Global configuration
_users               -- User accounts with authentication
```

### Blog Content Type (if selected)
```sql
posts          -- Blog posts with title, content, slug
categories     -- Post categories
tags           -- Post tags
post_tags      -- Many-to-many relationship
comments       -- Post comments
```

### Configuration File
```php
config.php     -- Database credentials, site settings, security keys
```

## 🔒 Security Features

- ✅ **Password hashing** - Bcrypt for admin password
- ✅ **Secret key generation** - Random 64-character key
- ✅ **Connection validation** - Test before proceeding
- ✅ **Transaction safety** - Rollback on error
- ✅ **Installer deletion** - Reminder to remove after install

## 🎨 Customization

### Modify Installer UI

Edit `/install/assets/installer.css` for styling:
```css
.installer-header {
    background: linear-gradient(135deg, #your-color 0%, #your-color 100%);
}
```

### Add Custom Steps

1. Add step to `InstallerWizard::$steps`
2. Create template in `/install/templates/your-step.php`
3. Update progress bar in `/install/index.php`

### Add Content Types

Implement `ContentType` interface and register in `InstallerWizard::getAvailableContentTypes()`.

## 🐛 Troubleshooting

### "System check failed"
- **PHP version too old**: Upgrade to PHP 8.0+
- **Missing extensions**: Install required PHP extensions
- **Directory not writable**: `chmod 755 cache/ examples/uploads/`

### "Database connection failed"
- **Wrong credentials**: Double-check username/password
- **Database doesn't exist**: Create database first
- **Host unreachable**: Check MySQL/PostgreSQL is running

### "Installation failed"
- **Check error message**: Specific error shown in alert
- **Check database logs**: Look for SQL errors
- **Try again**: Installer supports retry

### "Config file not created"
- **Permission denied**: Make root directory writable
- **Disk full**: Check available disk space

## 📚 Related Examples

- [Example 24: Blog CMS](../24-blog-cms/) - Complete blog implementation
- [Example 25: Themes](../25-themes/) - Theme system showcase
- [Example 26: WordPress Migration](../26-wordpress-migration/) - Migrate from WordPress

## 🎓 Learning Path

1. ✅ **Start here** - Install DynamicCRUD
2. [Example 01: Basic CRUD](../01-basic/) - Learn the basics
3. [Example 24: Blog CMS](../24-blog-cms/) - Build a blog
4. [Example 18: Admin Panel](../18-admin-panel/) - Create admin interface

## 💡 Tips

- **Test connection first** - Always test database before proceeding
- **Use strong passwords** - Minimum 8 characters for admin
- **Delete installer** - Remove `/install` directory after setup
- **Backup config.php** - Keep a copy of your configuration
- **Try CLI mode** - Faster for automated deployments

## 🚀 Next Steps

After installation:

1. **Delete installer directory**
   ```bash
   rm -rf install/
   ```

2. **Visit your site**
   ```
   http://localhost/
   ```

3. **Log in to admin**
   ```
   http://localhost/admin.php
   ```

4. **Customize settings**
   - Change theme
   - Add content
   - Configure metadata

5. **Read documentation**
   - [Quick Start Guide](../../docs/QUICKSTART.md)
   - [Best Practices](../../docs/BEST_PRACTICES.md)

## 📊 Performance

- **Installation time**: 2-5 seconds
- **Database queries**: ~10 queries
- **File operations**: 1 file (config.php)
- **Memory usage**: <5MB

## 🎉 Success!

You now have a fully functional DynamicCRUD installation ready to use!

**What makes this special:**
- 🚀 **60-second setup** - Faster than WordPress
- ✨ **Zero configuration** - Everything automated
- 🎨 **Professional UI** - Beautiful installer
- 🔒 **Secure by default** - Best practices built-in
- 🌐 **Multi-language** - International from day one

---

**Ready to build something amazing? Let's go! 🚀**
