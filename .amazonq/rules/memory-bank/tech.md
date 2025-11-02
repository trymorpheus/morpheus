# DynamicCRUD - Technology Stack

## Programming Languages

### PHP 8.0+
**Primary language** for all library code
- Modern PHP features: typed properties, constructor property promotion, match expressions
- Strict typing enabled in all classes
- PSR-4 autoloading standard
- Object-oriented architecture

### JavaScript (ES6+)
**Client-side validation** in `examples/assets/dynamiccrud.js`
- Real-time form validation
- AJAX form submission
- Image preview functionality
- DOM manipulation for dynamic feedback

### SQL
**Database language** for MySQL 5.7+
- DDL scripts for table creation
- Foreign key constraints
- ENUM field definitions
- Audit table schemas

### CSS3
**Styling** in `examples/assets/dynamiccrud.css`
- Responsive form layouts
- Validation feedback styling
- Accessibility enhancements

## Core Dependencies

### Required PHP Extensions
- **ext-pdo**: Database connectivity (PDO MySQL driver)
- **ext-fileinfo**: Real MIME type detection for file uploads
- **ext-json**: JSON parsing for metadata in column comments

### Development Dependencies
- **phpunit/phpunit** ^9.0: Unit testing framework

### Database
- **MySQL 5.7+**: Primary database system
- Uses INFORMATION_SCHEMA for schema introspection
- Requires InnoDB engine for foreign key support

## Build System

### Composer
**Package manager** for PHP dependencies and autoloading

**Key composer.json sections**:
```json
{
  "require": {
    "php": ">=8.0",
    "ext-pdo": "*",
    "ext-fileinfo": "*",
    "ext-json": "*"
  },
  "autoload": {
    "psr-4": {
      "DynamicCRUD\\": "src/"
    }
  }
}
```

### Installation Command
```bash
composer require dynamiccrud/dynamiccrud
```

### Autoloading
PSR-4 standard: `DynamicCRUD\ClassName` maps to `src/ClassName.php`

## Development Commands

### Install Dependencies
```bash
composer install
```

### Update Dependencies
```bash
composer update
```

### Run Tests
```bash
vendor/bin/phpunit tests/
```

### Clear Cache
```bash
php examples/clear_cache.php
```

### Database Setup
```bash
mysql -u user -p database < examples/setup.sql
mysql -u user -p database < examples/setup_phase2.sql
mysql -u user -p database < examples/setup_many_to_many.sql
mysql -u user -p database < examples/setup_audit.sql
```

## Architecture Patterns

### Design Patterns Used
- **Strategy Pattern**: Cache system (CacheStrategy interface)
- **Hook/Observer Pattern**: Lifecycle events system
- **Dependency Injection**: PDO connection injection
- **Single Responsibility**: Each class has one clear purpose
- **Factory Pattern**: Form element generation

### Database Patterns
- **Active Record-like**: Each table maps to CRUD operations
- **Repository Pattern**: SchemaAnalyzer acts as schema repository
- **Unit of Work**: Transaction management in CRUDHandler

## Security Technologies

### CSRF Protection
- Session-based token generation
- Token validation on all POST requests
- Automatic token regeneration

### SQL Injection Prevention
- PDO prepared statements exclusively
- No string concatenation in queries
- Parameter binding for all user input

### XSS Prevention
- htmlspecialchars() for output escaping
- Input sanitization in SecurityModule
- Content-Security-Policy headers recommended

### File Upload Security
- finfo_file() for real MIME type detection
- File size validation
- Unique filename generation with uniqid()
- Configurable allowed MIME types

## Performance Optimizations

### Caching System
- **FileCacheStrategy**: Stores schema metadata in cache/ directory
- Reduces INFORMATION_SCHEMA queries
- Configurable TTL (time-to-live)
- Cache invalidation on schema changes

### Database Optimization
- Prepared statement reuse
- Lazy loading of foreign key data
- Indexed audit tables
- Transaction batching for M:N sync

### Code Optimization
- Minimal object instantiation
- Efficient array operations
- Early returns to avoid unnecessary processing

## Testing Framework

### PHPUnit 9.0+
**Unit testing** for core components

**Test files**:
- `tests/SchemaAnalyzerTest.php`: Schema introspection tests
- `tests/ValidationEngineTest.php`: Validation logic tests

**Test coverage**: 98.75% of planned features tested

### Running Tests
```bash
vendor/bin/phpunit tests/
```

## Version Control

### Git
Repository hosted on GitHub: https://github.com/mcarbonell/DynamicCRUD

### Branching Strategy
- `main`: Stable releases
- Feature branches for development
- Pull requests for contributions

## Package Distribution

### Packagist
Official PHP package repository: https://packagist.org/packages/dynamiccrud/dynamiccrud

**Installation**:
```bash
composer require dynamiccrud/dynamiccrud
```

### Versioning
Semantic versioning (SemVer): MAJOR.MINOR.PATCH
- Current version: 1.0.0
- Breaking changes increment MAJOR
- New features increment MINOR
- Bug fixes increment PATCH

## Development Environment

### Minimum Requirements
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Composer 2.0+
- Web server (Apache/Nginx) with PHP support

### Recommended Setup
- PHP 8.1+ for latest features
- MySQL 8.0+ for better performance
- Xdebug for debugging
- PHPStan for static analysis

### IDE Support
- Full PSR-4 autoloading for IDE autocomplete
- Type hints for better IDE integration
- DocBlocks for all public methods

## Client-Side Technologies

### Vanilla JavaScript
No framework dependencies - pure JavaScript for:
- Form validation
- AJAX submissions
- Dynamic UI updates
- Image previews

### CSS Features
- Flexbox for layouts
- CSS transitions for animations
- Media queries for responsiveness
- CSS variables for theming

## Database Schema Introspection

### INFORMATION_SCHEMA Queries
Used to analyze table structure:
- `INFORMATION_SCHEMA.COLUMNS`: Column definitions
- `INFORMATION_SCHEMA.KEY_COLUMN_USAGE`: Foreign keys
- `INFORMATION_SCHEMA.TABLE_CONSTRAINTS`: Constraints

### Metadata Storage
JSON in `COLUMN_COMMENT` field:
```sql
ALTER TABLE users 
MODIFY COLUMN email VARCHAR(255) 
COMMENT '{"type": "email", "label": "Email Address"}';
```

## Transaction Management

### PDO Transactions
All write operations wrapped in transactions:
```php
$pdo->beginTransaction();
try {
    // INSERT/UPDATE/DELETE
    // Audit logging
    // M:N sync
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
}
```

### ACID Compliance
- **Atomicity**: All-or-nothing operations
- **Consistency**: Data integrity maintained
- **Isolation**: Concurrent transaction safety
- **Durability**: Committed changes persist

## File System Operations

### Upload Directory
Default: `uploads/` (configurable)
- Requires write permissions
- Unique filenames prevent conflicts
- Organized by upload date possible

### Cache Directory
Default: `cache/` (configurable)
- Stores serialized schema metadata
- Automatic cleanup on cache clear
- File-based locking for concurrency

## API Design

### Public API Methods
```php
// DynamicCRUD class
__construct(PDO $pdo, string $table)
renderForm(?int $id = null): string
handleSubmission(): array
renderList(int $page = 1, int $perPage = 10): string
addManyToMany(...): self
addHook(string $event, callable $callback): self
enableAudit(int $userId): self
```

### Fluent Interface
Method chaining supported:
```php
$crud->addHook('beforeSave', $callback)
     ->enableAudit($userId)
     ->handleSubmission();
```

## Error Handling

### Exception Types
- PDOException: Database errors
- RuntimeException: Configuration errors
- InvalidArgumentException: Invalid parameters

### Error Reporting
- Detailed error messages in development
- Generic messages in production
- Logging via error_log()

## Internationalization (i18n)

### Current Status
- English documentation complete
- Spanish documentation complete
- Code messages in English
- i18n support planned for v2.0

### Future i18n Support
- Translatable validation messages
- Locale-aware date/number formatting
- Multi-language form labels
