# DynamicCRUD - Development Guidelines

## Code Quality Standards

### PHP Coding Standards

#### Strict Typing
All PHP classes use strict type declarations:
```php
<?php

namespace DynamicCRUD;
```
- No explicit `declare(strict_types=1)` but typed properties and parameters enforced
- All class properties have explicit type declarations
- All method parameters and return types are typed

#### Type Declarations
Consistent use of PHP 8.0+ type hints:
```php
private PDO $pdo;
private string $table;
private array $schema;
private ?CacheStrategy $cache;  // Nullable types with ?
private ?int $userId = null;    // Nullable with default
```

#### Naming Conventions
- **Classes**: PascalCase (e.g., `CRUDHandler`, `SchemaAnalyzer`, `FormGenerator`)
- **Methods**: camelCase (e.g., `handleSubmission`, `renderForm`, `getTableSchema`)
- **Properties**: camelCase (e.g., `$uploadDir`, `$csrfToken`, `$manyToManyRelations`)
- **Constants**: UPPER_SNAKE_CASE (standard PHP constants)
- **Private methods**: camelCase with `private` visibility (e.g., `private function executeHook()`)

#### Method Visibility
Clear visibility modifiers on all methods:
- `public`: External API methods (renderForm, handleSubmission, addHook)
- `private`: Internal implementation details (save, update, findById, executeHook)
- No `protected` methods - composition over inheritance

#### Constructor Property Promotion
Not used - traditional constructor style with explicit property assignments:
```php
public function __construct(PDO $pdo, string $table, ?CacheStrategy $cache = null)
{
    $this->pdo = $pdo;
    $this->table = $table;
    $this->cache = $cache;
}
```

### Code Organization

#### Single Responsibility Principle
Each class has one clear purpose:
- `CRUDHandler`: CRUD operations and lifecycle management
- `FormGenerator`: HTML form rendering only
- `SchemaAnalyzer`: Database schema introspection only
- `ValidationEngine`: Data validation only
- `SecurityModule`: Security features only

#### Method Length
Methods are concise and focused:
- Most methods under 30 lines
- Complex operations broken into private helper methods
- Example: `handleSubmission()` delegates to `save()`, `update()`, `executeHook()`, `syncManyToManyRelations()`

#### File Structure Pattern
Consistent structure in all classes:
1. Namespace declaration
2. Use statements
3. Class declaration
4. Private properties
5. Constructor
6. Public API methods
7. Private helper methods

### Documentation Standards

#### Minimal Comments
Code is self-documenting through clear naming:
- No PHPDoc blocks on most methods
- No inline comments explaining obvious code
- Comments only for complex logic or non-obvious behavior

#### Method Names as Documentation
Method names clearly describe their purpose:
- `renderForm()` - renders a form
- `handleSubmission()` - processes form submission
- `getForeignKeyOptions()` - retrieves foreign key dropdown options
- `syncManyToManyRelations()` - synchronizes M:N pivot tables

## Semantic Patterns

### Database Interaction Patterns

#### Prepared Statements Exclusively
All SQL queries use PDO prepared statements with parameter binding:
```php
$sql = sprintf("SELECT * FROM %s WHERE %s = :id LIMIT 1", $this->table, $pk);
$stmt = $this->pdo->prepare($sql);
$stmt->execute(['id' => $id]);
```
**Frequency**: 100% of database queries (10+ occurrences)

#### NULL Handling
Explicit NULL parameter binding for nullable values:
```php
foreach ($data as $key => $value) {
    $stmt->bindValue(":{$key}", $value, $value === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
}
```
**Frequency**: All INSERT/UPDATE operations (3 occurrences)

#### Dynamic SQL Generation
Table and column names inserted via sprintf, values via parameters:
```php
$sql = sprintf(
    "INSERT INTO %s (%s) VALUES (%s)",
    $this->table,
    implode(', ', $columns),
    implode(', ', $placeholders)
);
```
**Frequency**: All CRUD operations (5+ occurrences)

### Transaction Management Pattern

#### Automatic Transactions
All write operations wrapped in transactions with rollback on error:
```php
try {
    $this->pdo->beginTransaction();
    
    // CRUD operation
    // Audit logging
    // M:N synchronization
    
    $this->pdo->commit();
    return ['success' => true, 'id' => $id];
    
} catch (\Exception $e) {
    $this->pdo->rollBack();
    return ['success' => false, 'error' => $e->getMessage()];
}
```
**Frequency**: All handleSubmission() and delete() methods (2 occurrences)

### Hook/Event System Pattern

#### Hook Registration
Fluent interface for registering hooks:
```php
public function beforeSave(callable $callback): self
{
    return $this->on('beforeSave', $callback);
}

public function on(string $event, callable $callback): self
{
    if (!isset($this->hooks[$event])) {
        $this->hooks[$event] = [];
    }
    $this->hooks[$event][] = $callback;
    return $this;
}
```
**Frequency**: 10 hook methods (beforeValidate, afterValidate, beforeSave, afterSave, etc.)

#### Hook Execution
Hooks modify data and return modified version:
```php
private function executeHook(string $event, ...$args)
{
    if (!isset($this->hooks[$event])) {
        return $args[0] ?? null;
    }
    
    $result = $args[0] ?? null;
    
    foreach ($this->hooks[$event] as $callback) {
        $result = $callback(...$args) ?? $result;
    }
    
    return $result;
}
```
**Frequency**: Called 10+ times in handleSubmission() flow

### Array Operations

#### Array Filtering and Mapping
Functional programming style with arrow functions:
```php
$allowedColumns = array_map(
    fn($col) => $col['name'],
    array_filter($this->schema['columns'], fn($col) => !$col['is_primary'])
);
```
**Frequency**: 5+ occurrences across codebase

#### Array Key Checking
Consistent use of isset() for array key existence:
```php
if (!isset($this->hooks[$event])) {
    return $args[0] ?? null;
}
```
**Frequency**: 20+ occurrences

### String Formatting

#### sprintf for SQL and HTML
Consistent use of sprintf for string formatting:
```php
$html = sprintf(
    '<input type="%s" name="%s" id="%s" value="%s"%s%s>',
    $type,
    $column['name'],
    $column['name'],
    htmlspecialchars($value),
    $attributes,
    $validationAttrs
);
```
**Frequency**: 15+ occurrences in FormGenerator

#### htmlspecialchars for Output
All user data escaped before HTML output:
```php
htmlspecialchars($value)
htmlspecialchars($label)
htmlspecialchars($tooltip)
```
**Frequency**: 20+ occurrences in FormGenerator

### Match Expressions

#### Modern PHP Match Syntax
Using match instead of switch for cleaner code:
```php
return match($column['sql_type']) {
    'int', 'bigint', 'smallint', 'tinyint' => 'number',
    'date' => 'date',
    'datetime', 'timestamp' => 'datetime-local',
    'text', 'longtext', 'mediumtext' => 'textarea',
    default => 'text'
};
```
**Frequency**: 3 occurrences (FileUploadHandler, FormGenerator)

### Caching Pattern

#### Cache-Aside Strategy
Check cache first, query database on miss, store result:
```php
$cacheKey = "schema_{$this->database}_{$table}";

if ($this->cache) {
    $cached = $this->cache->get($cacheKey);
    if ($cached !== null) {
        return $cached;
    }
}

// Query database
$schema = [...];

if ($this->cache) {
    $this->cache->set($cacheKey, $schema, $this->cacheTtl);
}

return $schema;
```
**Frequency**: 1 occurrence in SchemaAnalyzer

### Error Handling

#### Exception-Based Error Handling
Throw exceptions for errors, catch at boundaries:
```php
if (!is_writable($this->uploadDir)) {
    throw new \Exception("El directorio de uploads no tiene permisos de escritura");
}
```
**Frequency**: 10+ occurrences

#### Structured Error Returns
Return arrays with success/error information:
```php
return ['success' => false, 'error' => 'Token CSRF inválido'];
return ['success' => false, 'errors' => $validator->getErrors()];
return ['success' => true, 'id' => $id];
```
**Frequency**: 5 occurrences in handleSubmission()

## JavaScript Patterns

### Class-Based Architecture
ES6 classes for client-side validation:
```javascript
class DynamicCRUDValidator {
    constructor(formSelector = '.dynamic-crud-form') {
        this.form = document.querySelector(formSelector);
        if (!this.form) return;
        this.init();
    }
}
```

### Event Delegation
Event listeners on individual fields:
```javascript
field.addEventListener('blur', () => this.validateField(field));
field.addEventListener('input', () => {
    this.clearError(field);
    // Real-time validation
});
```

### Validation Methods
Separate methods for each validation type:
```javascript
isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

isValidUrl(url) {
    if (!/^https?:\/\//i.test(url)) {
        return false;
    }
    try {
        const urlObj = new URL(url);
        return urlObj.hostname.includes('.');
    } catch {
        return false;
    }
}
```

### DOM Manipulation
Direct DOM methods, no jQuery:
```javascript
field.classList.add('error');
field.classList.remove('error');
const errorDiv = document.createElement('div');
field.parentElement.appendChild(errorDiv);
```

## Security Patterns

### CSRF Token Pattern
Generate token on form render, validate on submission:
```php
// Generation
$csrfToken = $this->security->generateCsrfToken();

// Validation
if (!$this->security->validateCsrfToken($csrfToken)) {
    return ['success' => false, 'error' => 'Token CSRF inválido'];
}
```
**Frequency**: Every form render and submission

### Input Sanitization
Sanitize all POST data before processing:
```php
$data = $this->security->sanitizeInput($_POST, $allowedColumns, $this->schema);
```
**Frequency**: Every form submission

### File Upload Security
Real MIME type validation using finfo:
```php
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMimes)) {
    throw new \Exception("Tipo de archivo no permitido");
}
```
**Frequency**: Every file upload

## Common Idioms

### Null Coalescing Operator
Frequent use of ?? for default values:
```php
$value = $this->data[$column['name']] ?? $column['default_value'] ?? '';
$tooltip = $column['metadata']['tooltip'] ?? null;
$uploadDir = $uploadDir ?? __DIR__ . '/../examples/uploads';
```
**Frequency**: 30+ occurrences

### Ternary Operators
Concise conditional expressions:
```php
$selected = $value == $option['value'] ? ' selected' : '';
$requiredAttr = (!$column['is_nullable'] && !$value) ? ' required' : '';
$enctype = $this->hasFileFields() ? ' enctype="multipart/form-data"' : '';
```
**Frequency**: 15+ occurrences

### Array Destructuring in Loops
Not used - traditional foreach with key/value:
```php
foreach ($this->schema['columns'] as $column) {
    // Process column
}

foreach ($data as $key => $value) {
    // Process key-value pair
}
```

### String Concatenation
Concatenation with . operator, newlines with "\n":
```php
$html = '<form method="POST">' . "\n";
$html .= '  <input type="text">' . "\n";
$html .= '</form>' . "\n";
```
**Frequency**: 50+ occurrences in FormGenerator

## Architectural Patterns

### Dependency Injection
PDO connection injected into constructors:
```php
public function __construct(PDO $pdo, string $table)
{
    $this->pdo = $pdo;
    $this->table = $table;
}
```
**Frequency**: All classes that need database access

### Strategy Pattern
Cache system uses strategy interface:
```php
interface CacheStrategy {
    public function get(string $key);
    public function set(string $key, $value, int $ttl);
    public function invalidate(string $key): bool;
}

class FileCacheStrategy implements CacheStrategy {
    // Implementation
}
```
**Frequency**: 1 occurrence (Cache subsystem)

### Fluent Interface
Method chaining for configuration:
```php
$crud->addHook('beforeSave', $callback)
     ->enableAudit($userId)
     ->addManyToMany('tags', 'post_tags', 'post_id', 'tag_id', 'tags');
```
**Frequency**: All configuration methods return `$this`

### Composition Over Inheritance
No class inheritance - classes composed of other classes:
```php
class CRUDHandler {
    private SchemaAnalyzer $analyzer;
    private SecurityModule $security;
    private FileUploadHandler $fileHandler;
    private ?AuditLogger $auditLogger = null;
}
```
**Frequency**: All classes use composition

## Testing Patterns

### PHPUnit Test Structure
Standard PHPUnit test class structure:
```php
use PHPUnit\Framework\TestCase;

class SchemaAnalyzerTest extends TestCase {
    public function testGetTableSchema() {
        // Arrange
        // Act
        // Assert
    }
}
```

## Configuration Patterns

### Optional Parameters with Defaults
Nullable parameters with sensible defaults:
```php
public function __construct(
    PDO $pdo, 
    string $table, 
    ?CacheStrategy $cache = null, 
    ?string $uploadDir = null
)
```
**Frequency**: Most constructors

### Metadata in Database Comments
JSON configuration stored in column comments:
```sql
COMMENT '{"type": "email", "label": "Email Address", "tooltip": "Help text"}'
```
Parsed with:
```php
$decoded = json_decode($comment, true);
return is_array($decoded) ? $decoded : [];
```
**Frequency**: Core feature used throughout

## Performance Patterns

### Lazy Loading
Foreign key options loaded only when needed:
```php
private function getForeignKeyOptions(string $table, string $valueColumn, string $displayColumn): array
{
    if (!$this->pdo) {
        return [];
    }
    // Query only when rendering foreign key field
}
```

### Early Returns
Avoid unnecessary processing:
```php
if (!$this->form) return;
if (!isset($this->hooks[$event])) {
    return $args[0] ?? null;
}
```
**Frequency**: 10+ occurrences

### Prepared Statement Reuse
Prepare once, execute multiple times in loops:
```php
$stmt = $this->pdo->prepare($insertSql);
foreach ($selectedIds as $foreignId) {
    $stmt->execute(['local_id' => $id, 'foreign_id' => $foreignId]);
}
```
**Frequency**: M:N synchronization
