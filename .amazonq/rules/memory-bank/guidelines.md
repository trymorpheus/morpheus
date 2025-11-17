# Development Guidelines

## Code Quality Standards

### Formatting and Structure
- **PSR-4 Autoloading** - All classes follow PSR-4 namespace structure matching directory layout
- **Strict Types** - PHP 8.0+ features used throughout (named arguments, union types, nullsafe operator)
- **One Class Per File** - Each class in its own file matching the class name
- **Consistent Indentation** - 4 spaces for indentation, no tabs
- **Line Length** - Keep lines under 120 characters when practical
- **Blank Lines** - Single blank line between methods, two blank lines between classes

### Naming Conventions
- **Classes** - PascalCase (e.g., `FormGenerator`, `CRUDHandler`, `ValidationEngine`)
- **Methods** - camelCase (e.g., `renderForm()`, `handleSubmission()`, `validateData()`)
- **Private Methods** - camelCase with descriptive names (e.g., `validateField()`, `renderFormOpen()`)
- **Variables** - camelCase (e.g., `$csrfToken`, `$uploadDir`, `$tableMetadata`)
- **Constants** - UPPER_SNAKE_CASE (when used)
- **Database Tables** - lowercase_snake_case (e.g., `user_roles`, `post_tags`)
- **Database Columns** - lowercase_snake_case (e.g., `created_at`, `user_id`)

### Documentation Standards
- **Class Docblocks** - Not heavily used; code is self-documenting
- **Method Visibility** - Explicit public/private/protected keywords always used
- **Type Hints** - Strict type hints for parameters and return types
- **Nullable Types** - Use `?Type` syntax for nullable parameters/returns
- **Array Types** - Use `array` type hint with descriptive variable names

### Code Organization
- **Dependency Injection** - Constructor injection for dependencies (PDO, cache, etc.)
- **Fluent Interface** - Methods return `$this` for method chaining where appropriate
- **Single Responsibility** - Each method does one thing well
- **Small Methods** - Methods typically 5-30 lines, extracted when longer
- **Guard Clauses** - Early returns for validation/edge cases

## Architectural Patterns

### 1. Facade Pattern (Main API)
**Example: DynamicCRUD class**
```php
class DynamicCRUD
{
    private PDO $pdo;
    private CRUDHandler $handler;
    private SchemaAnalyzer $analyzer;
    
    public function renderForm(?int $id = null): string
    {
        // Orchestrates FormGenerator, SecurityModule, etc.
    }
    
    public function handleSubmission(): array
    {
        return $this->handler->handleSubmission($_POST, $_FILES);
    }
}
```
- Provides simple interface to complex subsystems
- Hides internal complexity from users
- Delegates to specialized components

### 2. Adapter Pattern (Database Abstraction)
**Example: DatabaseAdapter interface**
```php
interface DatabaseAdapter
{
    public function getTableSchema(string $table): array;
    public function getForeignKeys(string $table): array;
    public function quote(string $identifier): string;
}

class MySQLAdapter implements DatabaseAdapter { }
class PostgreSQLAdapter implements DatabaseAdapter { }
```
- Abstracts database-specific operations
- Allows easy addition of new databases
- Normalizes differences between databases

### 3. Strategy Pattern (Caching)
**Example: CacheStrategy interface**
```php
interface CacheStrategy
{
    public function get(string $key);
    public function set(string $key, $value, int $ttl = 3600): bool;
    public function delete(string $key): bool;
}

class FileCacheStrategy implements CacheStrategy { }
```
- Pluggable cache backends
- Easy to swap implementations
- Testable in isolation

### 4. Template Method Pattern (Themes)
**Example: AbstractTheme base class**
```php
abstract class AbstractTheme implements Theme
{
    abstract public function getName(): string;
    abstract public function getTemplates(): array;
    
    public function render(string $template, array $data): string
    {
        // Common rendering logic
    }
}
```
- Defines algorithm skeleton
- Subclasses override specific steps
- Promotes code reuse

### 5. Observer Pattern (Hooks/Events)
**Example: Hook system in CRUDHandler**
```php
private array $hooks = [];

public function on(string $event, callable $callback): self
{
    $this->hooks[$event][] = $callback;
    return $this;
}

private function executeHook(string $event, ...$args)
{
    foreach ($this->hooks[$event] as $callback) {
        $result = $callback(...$args) ?? $result;
    }
    return $result;
}
```
- Decouples event producers from consumers
- Allows custom logic injection
- 10 lifecycle hooks available

### 6. Builder Pattern (Fluent Interface)
**Example: Method chaining**
```php
$crud = new DynamicCRUD($pdo, 'users');
$crud->addManyToMany('tags', 'user_tags', 'user_id', 'tag_id', 'tags')
     ->addHook('beforeSave', fn($data) => $data)
     ->enableAudit($userId)
     ->setLocale('es');
```
- Readable configuration
- Methods return `$this`
- Progressive enhancement

## Common Implementation Patterns

### 1. Metadata-Driven Configuration
**Pattern: JSON in database comments**
```sql
-- Table metadata
CREATE TABLE posts (...) COMMENT = '{
    "display_name": "Blog Posts",
    "icon": "📝",
    "list_view": {"searchable": ["title"], "per_page": 20}
}';

-- Column metadata
ALTER TABLE users MODIFY COLUMN email VARCHAR(255) 
COMMENT '{"type": "email", "label": "Email Address", "required": true}';
```
**Usage in code:**
```php
$metadata = $column['metadata']['type'] ?? null;
if ($metadata === 'email') return 'email';
```
- Configuration stored with data structure
- No separate config files needed
- Self-documenting schema

### 2. Prepared Statements (Always)
**Pattern: SQL injection prevention**
```php
$sql = sprintf("SELECT * FROM %s WHERE %s = :id", $this->table, $pk);
$stmt = $this->pdo->prepare($sql);
$stmt->execute(['id' => $id]);
```
- Never concatenate user input into SQL
- Always use named parameters (`:param`)
- Bind values with appropriate types

### 3. Transaction Safety
**Pattern: Automatic rollback on error**
```php
try {
    $this->pdo->beginTransaction();
    
    // Multiple operations
    $id = $this->save($data);
    $this->syncManyToManyRelations($id);
    
    $this->pdo->commit();
    return ['success' => true, 'id' => $id];
    
} catch (\Exception $e) {
    $this->pdo->rollBack();
    return ['success' => false, 'error' => $e->getMessage()];
}
```
- Wrap multi-step operations in transactions
- Always rollback on exception
- Return structured results

### 4. Guard Clauses for Validation
**Pattern: Early returns**
```php
private function validateField(array $column, $value): void
{
    if ($this->isHiddenField($column)) {
        return; // Guard clause
    }
    
    if ($this->isRequiredAndEmpty($column, $value)) {
        $this->addRequiredError($column['name']);
        return; // Guard clause
    }
    
    if ($this->isEmpty($value)) {
        return; // Guard clause
    }
    
    // Main validation logic
    $this->validateType($column, $value);
    $this->validateLength($column, $value);
}
```
- Check edge cases first
- Return early to reduce nesting
- Main logic at bottom

### 5. Method Extraction for Clarity
**Pattern: Small, focused methods**
```php
// Before refactoring (250 lines)
public function handleSubmission(): array
{
    // Massive method with everything
}

// After refactoring (30 lines)
public function handleSubmission(): array
{
    if ($workflowResult = $this->handleWorkflowTransition()) return $workflowResult;
    if ($error = $this->validateCsrfToken()) return $error;
    if ($error = $this->checkPermissions($isUpdate)) return $error;
    
    try {
        $this->pdo->beginTransaction();
        $data = $this->prepareData();
        // ... more extracted methods
        $this->pdo->commit();
        return ['success' => true, 'id' => $id];
    } catch (\Exception $e) {
        $this->pdo->rollBack();
        return ['success' => false, 'error' => $e->getMessage()];
    }
}
```
- Extract complex logic into named methods
- Each method has single responsibility
- Self-documenting code

### 6. Nullsafe Operator Usage
**Pattern: Clean null handling**
```php
// Instead of:
if ($this->translator !== null) {
    $message = $this->translator->t('error.message');
}

// Use nullsafe operator:
$message = $this->translator?->t('error.message') ?? 'Default message';
```
- Reduces null checks
- Cleaner code
- PHP 8.0+ feature

### 7. Match Expressions
**Pattern: Type mapping**
```php
return match($column['sql_type']) {
    'int', 'bigint', 'smallint', 'tinyint' => 'number',
    'date' => 'date',
    'datetime', 'timestamp' => 'datetime-local',
    'text', 'longtext', 'mediumtext' => 'textarea',
    default => 'text'
};
```
- Cleaner than switch statements
- Returns value directly
- Exhaustive checking

### 8. Array Filtering and Mapping
**Pattern: Functional array operations**
```php
// Filter non-primary columns
$allowedColumns = array_map(
    fn($col) => $col['name'],
    array_filter($this->schema['columns'], fn($col) => !$col['is_primary'])
);

// Get non-primary columns
private function getNonPrimaryColumns(): array
{
    return array_filter(
        $this->schema['columns'],
        fn($col) => !$col['is_primary']
    );
}
```
- Use arrow functions for simple operations
- Prefer functional style over loops
- Readable and concise

### 9. Structured Return Values
**Pattern: Consistent result format**
```php
// Success
return ['success' => true, 'id' => $id];

// Error
return ['success' => false, 'error' => 'Error message'];

// Validation errors
return ['success' => false, 'errors' => ['field' => ['error1', 'error2']]];
```
- Always return arrays with 'success' key
- Include relevant data (id, errors, etc.)
- Consistent across all methods

### 10. Dependency Injection
**Pattern: Constructor injection**
```php
public function __construct(
    PDO $pdo,
    string $table,
    ?CacheStrategy $cache = null,
    ?string $uploadDir = null
) {
    $this->pdo = $pdo;
    $this->table = $table;
    $this->cache = $cache;
    $this->uploadDir = $uploadDir ?? __DIR__ . '/../examples/uploads';
}
```
- Required dependencies in constructor
- Optional dependencies with defaults
- No service locator pattern

## Internal API Usage

### SchemaAnalyzer - Database Schema Inspection
```php
$analyzer = new SchemaAnalyzer($pdo, $cache);
$schema = $analyzer->getTableSchema('users');

// Returns:
[
    'table' => 'users',
    'primary_key' => 'id',
    'columns' => [
        ['name' => 'id', 'sql_type' => 'int', 'is_primary' => true, ...],
        ['name' => 'email', 'sql_type' => 'varchar', 'metadata' => [...], ...]
    ],
    'foreign_keys' => ['user_id' => ['table' => 'users', 'column' => 'id']]
]
```

### FormGenerator - HTML Form Generation
```php
$generator = new FormGenerator($schema, $data, $csrfToken, $pdo, $handler);
$generator->setTranslator($translator);
$generator->setTableMetadata($metadata);
$html = $generator->render();
```

### ValidationEngine - Data Validation
```php
$validator = new ValidationEngine($schema, $translator);
if (!$validator->validate($data)) {
    $errors = $validator->getErrors();
    // ['field' => ['Error message 1', 'Error message 2']]
}
```

### CRUDHandler - Data Operations
```php
$handler = new CRUDHandler($pdo, 'users', $cache, $uploadDir);
$handler->setTranslator($translator);
$handler->on('beforeSave', fn($data) => $data);
$result = $handler->handleSubmission();
```

### TableMetadata - Metadata Access
```php
$metadata = new TableMetadata($pdo, 'users');
$metadata->hasTimestamps(); // bool
$metadata->isSluggable(); // bool
$metadata->getPermissions(); // array
$metadata->getNotificationConfig(); // array
```

## Frequently Used Code Idioms

### 1. Null Coalescing with Default
```php
$uploadDir = $uploadDir ?? __DIR__ . '/../examples/uploads';
$locale = $locale ?? 'en';
$type = $column['metadata']['type'] ?? null;
```

### 2. Ternary for Simple Conditionals
```php
$selected = $value == $option['value'] ? ' selected' : '';
$required = !$column['is_nullable'] ? ' required' : '';
$enctype = $this->hasFileFields() ? ' enctype="multipart/form-data"' : '';
```

### 3. Sprintf for SQL/HTML Generation
```php
$sql = sprintf("SELECT * FROM %s WHERE %s = :id", $this->table, $pk);
$html = sprintf('<input type="%s" name="%s" value="%s">', $type, $name, htmlspecialchars($value));
```

### 4. Array Destructuring in Loops
```php
foreach ($this->schema['columns'] as $column) {
    $name = $column['name'];
    $type = $column['sql_type'];
}
```

### 5. Conditional Assignment
```php
if ($workflowResult = $this->handleWorkflowTransition()) {
    return $workflowResult;
}
```

### 6. String Concatenation with Newlines
```php
$html = '<form>' . "\n";
$html .= '  <input type="text">' . "\n";
$html .= '</form>' . "\n";
```

### 7. Empty Array Initialization
```php
private array $hooks = [];
private array $errors = [];
private array $manyToManyRelations = [];
```

### 8. Method Chaining Return
```php
public function setTranslator(Translator $translator): self
{
    $this->translator = $translator;
    return $this;
}
```

## Security Best Practices

### 1. CSRF Protection
```php
// Generate token
$csrfToken = $this->security->generateCsrfToken();

// Validate token
if (!$this->security->validateCsrfToken($_POST['csrf_token'] ?? '')) {
    return ['success' => false, 'error' => 'Invalid CSRF token'];
}
```

### 2. XSS Prevention
```php
// Always escape output
echo htmlspecialchars($value);
echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
```

### 3. SQL Injection Prevention
```php
// Always use prepared statements
$stmt = $this->pdo->prepare($sql);
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
```

### 4. File Upload Security
```php
// Real MIME type validation
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $tmpPath);
finfo_close($finfo);

// Whitelist allowed types
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($mimeType, $allowedTypes)) {
    throw new \Exception('Invalid file type');
}
```

### 5. Password Hashing
```php
// Hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Verify password
if (password_verify($password, $hash)) {
    // Valid
}
```

## Testing Patterns

### 1. Test Structure
```php
class DynamicCRUDTest extends TestCase
{
    private PDO $pdo;
    
    protected function setUp(): void
    {
        $this->pdo = TestHelper::createTestDatabase();
    }
    
    public function testBasicCRUD(): void
    {
        // Arrange
        $crud = new DynamicCRUD($this->pdo, 'users');
        
        // Act
        $result = $crud->handleSubmission();
        
        // Assert
        $this->assertTrue($result['success']);
    }
}
```

### 2. Test Helpers
```php
class TestHelper
{
    public static function createTestDatabase(): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}
```

## Performance Optimization

### 1. Caching Strategy
```php
// Check cache first
if ($cached = $this->cache?->get($cacheKey)) {
    return $cached;
}

// Compute and cache
$result = $this->computeExpensiveOperation();
$this->cache?->set($cacheKey, $result, 3600);
return $result;
```

### 2. Lazy Loading
```php
// Only load when needed
private ?Translator $translator = null;

public function getTranslator(): Translator
{
    if ($this->translator === null) {
        $this->translator = new Translator();
    }
    return $this->translator;
}
```

### 3. Batch Operations
```php
// Insert multiple records in one transaction
$this->pdo->beginTransaction();
foreach ($records as $record) {
    $stmt->execute($record);
}
$this->pdo->commit();
```

## Error Handling

### 1. Try-Catch with Rollback
```php
try {
    $this->pdo->beginTransaction();
    // Operations
    $this->pdo->commit();
} catch (\Exception $e) {
    $this->pdo->rollBack();
    throw $e;
}
```

### 2. Graceful Degradation
```php
// Try preferred method, fallback to alternatives
try {
    return $this->getForeignKeyOptions($table, $displayColumn);
} catch (\PDOException $e) {
    // Try alternative columns
    foreach ($possibleColumns as $column) {
        try {
            return $this->getForeignKeyOptions($table, $column);
        } catch (\PDOException $e) {
            continue;
        }
    }
    // Last resort
    return $this->getForeignKeyOptionsById($table);
}
```

### 3. Validation Error Collection
```php
private array $errors = [];

private function validateField(array $column, $value): void
{
    if ($error = $this->checkRequired($column, $value)) {
        $this->errors[$column['name']][] = $error;
    }
    if ($error = $this->checkType($column, $value)) {
        $this->errors[$column['name']][] = $error;
    }
}
```

## Minimal Code Philosophy

### Core Principle
Write only the absolute minimal amount of code needed to address requirements correctly. Avoid verbose implementations and any code that doesn't directly contribute to the solution.

### Examples
```php
// ❌ Verbose
public function isActive(): bool
{
    if ($this->status === 'active') {
        return true;
    } else {
        return false;
    }
}

// ✅ Minimal
public function isActive(): bool
{
    return $this->status === 'active';
}

// ❌ Verbose
if ($user !== null) {
    if ($user->hasPermission('admin')) {
        return true;
    }
}
return false;

// ✅ Minimal
return $user?->hasPermission('admin') ?? false;
```

### Guidelines
- Use modern PHP features (nullsafe, match, arrow functions)
- Prefer functional style over imperative loops
- Extract methods only when it improves clarity
- Avoid unnecessary abstractions
- No code comments unless absolutely necessary (code should be self-documenting)
