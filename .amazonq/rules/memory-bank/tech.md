# Technology Stack

## Programming Languages

### PHP 8.0+
- **Primary Language** - All core code written in PHP
- **Version Requirement** - PHP 8.0 or higher
- **Modern Features Used:**
  - Named arguments
  - Union types
  - Nullsafe operator (?->)
  - Match expressions
  - Constructor property promotion
  - Attributes

### SQL
- **MySQL 5.7+** - Primary database support
- **PostgreSQL 12+** - Secondary database support
- **Features Used:**
  - Foreign keys
  - JSON column comments (metadata storage)
  - Prepared statements
  - Transactions

### JavaScript
- **Client-side Validation** - Form validation
- **UI Enhancements** - Drag & drop, dynamic forms
- **No Framework** - Vanilla JavaScript only

### HTML5 & CSS3
- **Semantic HTML** - Accessible markup
- **Modern CSS** - Flexbox, Grid, CSS variables
- **Responsive Design** - Mobile-first approach

## Core Technologies

### Database Layer
- **PDO (PHP Data Objects)** - Database abstraction
- **Prepared Statements** - SQL injection prevention
- **Transactions** - Data integrity
- **Adapter Pattern** - Multi-database support

### Template Engine
- **Custom Blade-like Engine** - Template compilation
- **Features:**
  - Layout inheritance (@extends, @section, @yield)
  - Partials (@include)
  - Automatic escaping ({{ }} vs {!! !!})
  - File caching for performance
  - Conditional rendering (@if, @foreach)

### Authentication
- **bcrypt** - Password hashing (PASSWORD_DEFAULT)
- **Sessions** - User session management
- **CSRF Tokens** - Cross-site request forgery protection
- **Rate Limiting** - Brute force prevention

### API
- **JWT (JSON Web Tokens)** - API authentication
- **RESTful Design** - Standard HTTP methods
- **OpenAPI/Swagger** - API documentation

### File Handling
- **finfo** - Real MIME type detection
- **GD Library** - Image manipulation (resize, crop, thumbnails)
- **JSON Storage** - Multiple file paths

### Caching
- **File-based Cache** - Schema metadata caching
- **Template Cache** - Compiled template storage
- **Strategy Pattern** - Pluggable cache backends

## Build System

### Composer
- **Package Manager** - Dependency management
- **Autoloading** - PSR-4 autoloading
- **Scripts** - Custom commands

**composer.json:**
```json
{
    "name": "trymorpheus/morpheus",
    "type": "library",
    "require": {
        "php": ">=8.0",
        "ext-pdo": "*",
        "ext-fileinfo": "*",
        "ext-json": "*"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.5 || ^10.0"
    },
    "autoload": {
        "psr-4": {
            "Morpheus\\": "src/"
        }
    }
}
```

### No Build Process
- **Zero Compilation** - Pure PHP, no transpilation
- **No Bundlers** - No webpack, vite, etc.
- **No Node.js** - No npm dependencies
- **Instant Deploy** - Copy files and go

## Testing Framework

### PHPUnit
- **Version:** 9.5+ or 10.0+
- **Test Count:** 478 tests
- **Assertions:** 1070+ assertions
- **Coverage:** 90%
- **Pass Rate:** 100%

**phpunit.xml:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php"
         colors="true"
         verbose="true">
    <testsuites>
        <testsuite name="DynamicCRUD Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

## Development Tools

### CLI Tool
**Location:** bin/morpheus

**20+ Commands:**
- `init` - Initialize project
- `install` - Run installer
- `list:tables` - List database tables
- `generate:metadata` - Generate metadata for table
- `validate:metadata` - Validate table metadata
- `clear:cache` - Clear all caches
- `export:csv` - Export table to CSV
- `import:csv` - Import CSV to table
- `dump:sql` - Export SQL dump
- `import:sql` - Import SQL dump
- `config:set` - Set global configuration
- `config:get` - Get global configuration
- `config:list` - List all configuration
- `config:delete` - Delete configuration
- `webhook:configure` - Configure webhook
- `test:webhook` - Test webhook
- `test:connection` - Test database connection
- `metadata:export` - Export metadata to JSON
- `metadata:import` - Import metadata from JSON
- `wordpress:migrate` - Migrate from WordPress

### Docker Support
**docker-compose.yml** - MySQL and PostgreSQL containers

```yaml
version: '3.8'
services:
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: test
    ports:
      - "3306:3306"
  
  postgres:
    image: postgres:14
    environment:
      POSTGRES_PASSWORD: rootpassword
      POSTGRES_DB: test
    ports:
      - "5432:5432"
```

### Version Control
- **Git** - Source control
- **GitHub** - Repository hosting
- **GitHub Actions** - CI/CD pipeline

## CI/CD Pipeline

### GitHub Actions
**Workflows:**
1. **Tests** - Run PHPUnit on PHP 8.0, 8.1, 8.2, 8.3
2. **Code Quality** - Static analysis
3. **Release** - Automated releases

**.github/workflows/tests.yml:**
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php: ['8.0', '8.1', '8.2', '8.3']
    steps:
      - uses: actions/checkout@v2
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
      - run: composer install
      - run: vendor/bin/phpunit
```

## Development Commands

### Installation
```bash
# Install dependencies
composer install

# Install globally
composer global require trymorpheus/morpheus
```

### Testing
```bash
# Run all tests
php vendor/phpunit/phpunit/phpunit

# Run specific test
php vendor/phpunit/phpunit/phpunit tests/DynamicCRUDTest.php

# Run with coverage
php vendor/phpunit/phpunit/phpunit --coverage-html coverage/

# Windows batch file
run-tests.bat
```

### CLI Commands
```bash
# Initialize project
php bin/morpheus init

# Run installer (interactive)
php bin/morpheus install

# Run installer (non-interactive)
php bin/morpheus install \
  --db-host=localhost \
  --db-name=mysite \
  --db-user=root \
  --db-pass=password \
  --site-name="My Blog" \
  --admin-email=admin@example.com \
  --admin-pass=secure123 \
  --content-type=blog \
  --theme=modern

# List tables
php bin/morpheus list:tables

# Generate metadata
php bin/morpheus generate:metadata users

# Clear cache
php bin/morpheus clear:cache

# Export data
php bin/morpheus export:csv users --output=users.csv

# Import data
php bin/morpheus import:csv users data.csv

# SQL dump
php bin/morpheus dump:sql users --output=users.sql

# WordPress migration
php bin/morpheus wordpress:migrate export.xml
```

### Docker Commands
```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# Execute SQL (MySQL)
docker-compose exec mysql mysql -uroot -prootpassword test

# Execute SQL (PostgreSQL)
docker-compose exec postgres psql -U postgres test
```

### Cache Management
```bash
# Clear all caches
php bin/morpheus clear:cache

# Clear template cache
rm -rf cache/templates/*

# Clear schema cache
rm -rf cache/*.cache

# PHP script
php examples/clear_cache.php
```

## Database Configuration

### MySQL
```php
$pdo = new PDO(
    'mysql:host=localhost;dbname=test;charset=utf8mb4',
    'root',
    'rootpassword',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);
```

### PostgreSQL
```php
$pdo = new PDO(
    'pgsql:host=localhost;dbname=test',
    'postgres',
    'rootpassword',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);
```

### Windows Development (SSL Issues)
```php
// For development only - disable SSL verification
$pdo = new PDO(
    'mysql:host=localhost;dbname=test',
    'root',
    'rootpassword'
);
// Add CURLOPT_SSL_VERIFYPEER=false for cURL operations
```

## Project Configuration

### Metadata Storage
**JSON in Table Comments:**
```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    title VARCHAR(255)
) COMMENT = '{
    "display_name": "Blog Posts",
    "icon": "📝",
    "list_view": {"searchable": ["title"], "per_page": 20}
}';
```

**JSON in Column Comments:**
```sql
ALTER TABLE users 
MODIFY COLUMN email VARCHAR(255) 
COMMENT '{"type": "email", "label": "Email Address", "required": true}';
```

### Global Configuration
**Stored in _metadata table:**
```php
$config = new GlobalMetadata($pdo);
$config->set('application.name', 'My App');
$config->set('theme.primary_color', '#667eea');
```

### Theme Configuration
**CSS Variables:**
```css
:root {
    --primary-color: #667eea;
    --secondary-color: #764ba2;
    --text-color: #333;
    --background-color: #fff;
}
```

## Performance Optimization

### Caching Strategy
- **Schema Cache** - Database structure cached
- **Template Cache** - Compiled templates cached
- **Query Cache** - Frequent queries cached
- **File-based** - No external cache server needed

### Database Optimization
- **Prepared Statements** - Query plan caching
- **Indexes** - Foreign keys indexed
- **Transactions** - Batch operations
- **Connection Pooling** - Reuse connections

### Code Optimization
- **Lazy Loading** - Load only what's needed
- **Minimal Dependencies** - No bloat
- **Efficient Queries** - Optimized SQL
- **Autoloading** - PSR-4 autoloading

## Security Measures

### Input Validation
- **Server-side** - PHP validation
- **Client-side** - JavaScript validation
- **Type Checking** - Strict types
- **Sanitization** - XSS prevention

### Database Security
- **Prepared Statements** - SQL injection prevention
- **Transactions** - Data integrity
- **Permissions** - Row-level security
- **Audit Logging** - Change tracking

### Authentication Security
- **bcrypt** - Strong password hashing
- **CSRF Tokens** - Request forgery prevention
- **Rate Limiting** - Brute force prevention
- **Session Security** - Secure session handling

### File Upload Security
- **MIME Validation** - Real file type checking (finfo)
- **Size Limits** - Prevent large uploads
- **Extension Whitelist** - Allowed file types
- **Secure Storage** - Outside web root

## Deployment

### Requirements
- PHP 8.0+ with PDO, fileinfo, json extensions
- MySQL 5.7+ or PostgreSQL 12+
- Web server (Apache, Nginx)
- Write permissions for cache/ and uploads/

### Installation Steps
1. `composer require dynamiccrud/dynamiccrud`
2. Navigate to `/install/` in browser
3. Follow 8-step wizard
4. Start using!

### Production Checklist
- Enable caching
- Disable debug mode
- Set secure passwords
- Configure backups
- Enable HTTPS
- Set proper file permissions
- Configure error logging

## Development Environment

### Recommended Setup
- **OS:** Windows, macOS, or Linux
- **PHP:** 8.0+ with extensions
- **Database:** MySQL 8.0 or PostgreSQL 14
- **Web Server:** Apache or Nginx
- **IDE:** VS Code, PhpStorm, or similar
- **Docker:** For database containers

### Local Development
```bash
# Clone repository
git clone https://github.com/trymorpheus/morpheus.git

# Install dependencies
composer install

# Start Docker containers
docker-compose up -d

# Run tests
php vendor/phpunit/phpunit/phpunit

# Start development server
php -S localhost:8000 -t examples/
```

## Version Control

### Git Workflow
- **main** - Production-ready code
- **develop** - Development branch
- **feature/** - Feature branches
- **release/** - Release branches

### Release Process
1. Update version in composer.json
2. Update CHANGELOG.md
3. Create release notes
4. Tag release
5. Push to GitHub
6. Publish to Packagist

## Package Distribution

### Packagist
- **Package:** trymorpheus/morpheus
- **URL:** https://packagist.org/packages/trymorpheus/morpheus
- **Auto-update:** GitHub webhook

### GitHub Releases
- **Repository:** https://github.com/trymorpheus/morpheus
- **Tags:** Semantic versioning (v4.0.0)
- **Assets:** Source code archives
- **Release Notes:** Detailed changelog
