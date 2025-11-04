# Best Practices

Production-ready patterns and recommendations for DynamicCRUD.

---

## Security Best Practices

### 1. Always Use HTTPS in Production

```php
// Force HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}
```

### 2. Validate User Input

```php
// Use validation rules
$crud->beforeValidate(function($data) {
    // Additional validation
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new \Exception('Invalid email');
    }
    return $data;
});
```

### 3. Implement Rate Limiting

```php
// For authentication
$crud->enableAuthentication();
// Built-in rate limiting: 5 attempts, 15-minute lockout
```

### 4. Use Environment Variables

```php
// Don't hardcode credentials
$pdo = new PDO(
    "mysql:host=" . getenv('DB_HOST'),
    getenv('DB_USER'),
    getenv('DB_PASS')
);
```

### 5. Enable Audit Logging

```php
$crud->enableAudit($userId);
// Track all changes with user, IP, timestamp
```

---

## Performance Best Practices

### 1. Enable Caching

```php
use DynamicCRUD\Cache\FileCacheStrategy;

$cache = new FileCacheStrategy(__DIR__ . '/cache');
$crud = new DynamicCRUD($pdo, 'users', cache: $cache);
```

### 2. Use Pagination

```php
// List with pagination
echo $crud->renderList(['per_page' => 50]);
```

### 3. Optimize Queries

```php
// Export with filters
$csv = $crud->export('csv', [
    'where' => ['status' => 'active'],
    'limit' => 1000
]);
```

### 4. Lazy Load Relationships

```php
// Only load M:N when needed
if ($id) {
    $crud->addManyToMany('tags', 'post_tags', 'post_id', 'tag_id', 'tags');
}
```

### 5. Database Indexing

```sql
-- Index foreign keys
CREATE INDEX idx_post_user ON posts(user_id);

-- Index searchable fields
CREATE INDEX idx_user_email ON users(email);

-- Composite indexes for unique_together
CREATE UNIQUE INDEX idx_sku_category ON products(sku, category);
```

---

## Code Organization

### 1. Separate Configuration

```php
// config/database.php
return [
    'host' => getenv('DB_HOST'),
    'database' => getenv('DB_NAME'),
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PASS')
];

// app.php
$config = require 'config/database.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['database']}",
    $config['username'],
    $config['password']
);
```

### 2. Use Service Classes

```php
class UserService
{
    private DynamicCRUD $crud;
    
    public function __construct(PDO $pdo)
    {
        $this->crud = new DynamicCRUD($pdo, 'users');
        $this->crud->enableAuthentication();
        $this->setupHooks();
    }
    
    private function setupHooks(): void
    {
        $this->crud->beforeSave(function($data) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            return $data;
        });
    }
    
    public function register(array $data): array
    {
        return $this->crud->handleAuthentication();
    }
}
```

### 3. Centralize Hooks

```php
// hooks/UserHooks.php
class UserHooks
{
    public static function beforeSave($data)
    {
        $data['slug'] = self::generateSlug($data['name']);
        return $data;
    }
    
    public static function afterCreate($id, $data)
    {
        // Send welcome email
        mail($data['email'], 'Welcome!', 'Thanks for registering');
    }
    
    private static function generateSlug($name)
    {
        return strtolower(str_replace(' ', '-', $name));
    }
}

// Usage
$crud->beforeSave([UserHooks::class, 'beforeSave']);
$crud->afterCreate([UserHooks::class, 'afterCreate']);
```

---

## Database Design

### 1. Use Metadata Comments

```sql
-- Table metadata
ALTER TABLE users COMMENT = '{
    "display_name": "Users",
    "icon": "👥",
    "permissions": {
        "create": ["admin"],
        "read": ["*"],
        "update": ["owner", "admin"],
        "delete": ["admin"]
    }
}';

-- Column metadata
ALTER TABLE users 
MODIFY COLUMN email VARCHAR(255) 
COMMENT '{"type": "email", "label": "Email Address", "tooltip": "Required"}';
```

### 2. Consistent Naming

```sql
-- Use snake_case
CREATE TABLE user_profiles (
    id INT PRIMARY KEY,
    user_id INT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Consistent foreign key naming
FOREIGN KEY (user_id) REFERENCES users(id)
```

### 3. Add Timestamps

```sql
ALTER TABLE posts ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE posts ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

ALTER TABLE posts COMMENT = '{
    "behaviors": {
        "timestamps": {
            "created_at": "created_at",
            "updated_at": "updated_at"
        }
    }
}';
```

### 4. Implement Soft Deletes

```sql
ALTER TABLE posts ADD COLUMN deleted_at TIMESTAMP NULL;

ALTER TABLE posts COMMENT = '{
    "behaviors": {
        "soft_deletes": {"column": "deleted_at"}
    }
}';
```

---

## Error Handling

### 1. Graceful Degradation

```php
try {
    $result = $crud->handleSubmission();
    if ($result['success']) {
        header('Location: success.php');
    } else {
        $errors = $result['errors'] ?? [$result['error']];
        // Display errors
    }
} catch (\Exception $e) {
    error_log($e->getMessage());
    echo "An error occurred. Please try again.";
}
```

### 2. Validation Error Display

```php
if (isset($result['errors'])) {
    foreach ($result['errors'] as $field => $error) {
        echo "<div class='error'>$field: $error</div>";
    }
}
```

### 3. Log Errors

```php
$crud->afterSave(function($id, $data) {
    try {
        // Some operation
    } catch (\Exception $e) {
        error_log("Error in afterSave: " . $e->getMessage());
    }
});
```

---

## Testing

### 1. Test Database Setup

```php
class UserCRUDTest extends TestCase
{
    private PDO $pdo;
    private DynamicCRUD $crud;
    
    protected function setUp(): void
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'pass');
        $this->crud = new DynamicCRUD($this->pdo, 'users');
        $this->cleanupTestData();
    }
    
    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }
    
    private function cleanupTestData(): void
    {
        $this->pdo->exec("DELETE FROM users WHERE email LIKE 'test_%'");
    }
}
```

### 2. Test Hooks

```php
public function testBeforeSaveHook(): void
{
    $called = false;
    
    $this->crud->beforeSave(function($data) use (&$called) {
        $called = true;
        return $data;
    });
    
    $_POST = ['name' => 'Test', 'email' => 'test@example.com'];
    $this->crud->handleSubmission();
    
    $this->assertTrue($called);
}
```

### 3. Integration Tests

```php
public function testCompleteFlow(): void
{
    // Create
    $_POST = ['name' => 'Test User', 'email' => 'test@example.com'];
    $result = $this->crud->handleSubmission();
    $this->assertTrue($result['success']);
    $id = $result['id'];
    
    // Read
    $user = $this->pdo->query("SELECT * FROM users WHERE id = $id")->fetch();
    $this->assertEquals('Test User', $user['name']);
    
    // Update
    $_POST = ['id' => $id, 'name' => 'Updated User'];
    $result = $this->crud->handleSubmission();
    $this->assertTrue($result['success']);
    
    // Delete
    $this->crud->delete($id);
    $user = $this->pdo->query("SELECT * FROM users WHERE id = $id")->fetch();
    $this->assertFalse($user);
}
```

---

## Deployment

### 1. Environment Configuration

```bash
# .env
DB_HOST=localhost
DB_NAME=production_db
DB_USER=prod_user
DB_PASS=secure_password
APP_ENV=production
```

### 2. Cache Warming

```bash
# After deployment
php bin/dynamiccrud clear:cache
php bin/dynamiccrud list:tables  # Warm cache
```

### 3. Database Migrations

```bash
# Export metadata from dev
php bin/dynamiccrud metadata:export users --output=users.json

# Import to production
php bin/dynamiccrud metadata:import users.json
```

### 4. Monitoring

```php
// Log all errors
$crud->afterSave(function($id, $data) {
    if (getenv('APP_ENV') === 'production') {
        // Send to monitoring service
    }
});
```

---

## Internationalization

### 1. Set Locale

```php
// Auto-detect from browser
$crud = new DynamicCRUD($pdo, 'users');

// Or force specific locale
$crud->setLocale('es');
```

### 2. Custom Translations

```php
$translator = $crud->getTranslator();
$translator->addTranslations('es', [
    'custom.message' => 'Mensaje personalizado'
]);
```

---

## Maintenance

### 1. Regular Cache Clearing

```bash
# Cron job
0 0 * * * cd /path/to/project && php bin/dynamiccrud clear:cache
```

### 2. Backup Metadata

```bash
# Weekly backup
php bin/dynamiccrud metadata:export users --output=backups/users_$(date +%Y%m%d).json
```

### 3. Monitor Performance

```php
$start = microtime(true);
$result = $crud->handleSubmission();
$duration = microtime(true) - $start;

if ($duration > 1.0) {
    error_log("Slow operation: {$duration}s");
}
```

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
