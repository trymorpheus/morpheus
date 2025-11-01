# Installation Guide - DynamicCRUD

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- PDO MySQL extension enabled
- fileinfo extension enabled (for file uploads)

## Installation via Composer (Recommended)

```bash
composer require dynamiccrud/dynamiccrud
```

## Manual Installation

### 1. Clone or download the project

```bash
git clone https://github.com/mcarbonell/DynamicCRUD.git
cd DynamicCRUD
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure database

Run the example SQL script:

```bash
mysql -u root -p < examples/setup.sql
```

Or manually:

```sql
CREATE DATABASE test;
USE test;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL COMMENT '{"label": "Full Name"}',
    email VARCHAR(255) NOT NULL COMMENT '{"type": "email", "label": "Email Address"}',
    website VARCHAR(255) COMMENT '{"type": "url", "label": "Website"}',
    age INT COMMENT '{"label": "Age"}',
    bio TEXT COMMENT '{"label": "Biography"}',
    birth_date DATE COMMENT '{"label": "Birth Date"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 4. Configure connection

Edit `examples/index.php` with your credentials:

```php
$pdo = new PDO('mysql:host=localhost;dbname=test', 'your_user', 'your_password');
```

### 5. Test the example

Start a PHP server:

```bash
php -S localhost:8000 -t examples
```

Open in your browser: http://localhost:8000

## Usage in Your Project

### Basic Setup

```php
<?php
require_once 'vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=your_db', 'user', 'password');
$crud = new DynamicCRUD($pdo, 'your_table');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    if ($result['success']) {
        echo "Saved with ID: {$result['id']}";
    }
} else {
    echo $crud->renderForm();
}
```

### With Cache (Recommended for Production)

```php
use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=your_db', 'user', 'password');
$cache = new FileCacheStrategy(__DIR__ . '/cache', 3600); // 1 hour TTL

$crud = new DynamicCRUD($pdo, 'your_table', $cache);
```

### With File Uploads

```php
$crud = new DynamicCRUD(
    $pdo, 
    'products', 
    null, 
    __DIR__ . '/uploads' // Custom upload directory
);
```

## Configuration

### PHP Settings

For file uploads, ensure these settings in `php.ini`:

```ini
file_uploads = On
upload_max_filesize = 10M
post_max_size = 10M
extension=fileinfo
extension=pdo_mysql
```

### Directory Permissions

```bash
# Create and set permissions for cache and uploads
mkdir -p cache uploads
chmod 755 cache uploads
```

## Verify Installation

Create a test file `test.php`:

```php
<?php
require 'vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

try {
    $pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
    $crud = new DynamicCRUD($pdo, 'users');
    echo "✓ DynamicCRUD installed successfully!";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}
```

Run: `php test.php`

## Troubleshooting

### "Class 'DynamicCRUD\DynamicCRUD' not found"
- Run `composer install`
- Check that `vendor/autoload.php` is included

### "Extension 'fileinfo' not found"
- Enable in `php.ini`: `extension=fileinfo`
- Restart web server

### "SQLSTATE[HY000] [2002] Connection refused"
- Check MySQL is running
- Verify host, user, and password
- Check firewall settings

### Cache not working
- Ensure `cache/` directory exists and is writable
- Check permissions: `chmod 755 cache`

## Next Steps

- Customize form CSS styles
- Add custom metadata to your tables
- Review complete documentation in [README.md](README.md)
- Explore [examples/](examples/) directory
- Read [docs/HOOKS.en.md](docs/HOOKS.en.md) for advanced features

## Running Tests

```bash
php tests/SchemaAnalyzerTest.php
```

## Production Deployment

1. **Enable caching**:
```php
$cache = new FileCacheStrategy(__DIR__ . '/cache', 3600);
$crud = new DynamicCRUD($pdo, 'table', $cache);
```

2. **Use HTTPS** for file uploads and sensitive data

3. **Set proper permissions**:
```bash
chmod 755 cache uploads
chmod 644 *.php
```

4. **Configure PHP for production**:
```ini
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
```

5. **Clear cache after schema changes**:
```bash
rm -rf cache/*.json
```

---

**Need help?** Open an issue on [GitHub](https://github.com/mcarbonell/DynamicCRUD/issues)
