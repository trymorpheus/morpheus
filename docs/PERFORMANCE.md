# Performance & Memory Optimization Guide

This guide covers performance optimization and memory management best practices for DynamicCRUD.

## Query Caching

### In-Memory Query Cache

DynamicCRUD includes `QueryCache` for caching query results within a single request:

```php
use Morpheus\Cache\QueryCache;

$queryCache = new QueryCache();

// Generate cache key
$key = $queryCache->generateKey($sql, $params);

// Check cache
if ($queryCache->has($key)) {
    $result = $queryCache->get($key);
} else {
    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Cache result
    $queryCache->set($key, $result);
}

// View statistics
$stats = $queryCache->getStats();
// ['hits' => 10, 'misses' => 3, 'size' => 3, 'hit_rate' => 76.92]
```

**Benefits:**
- Eliminates duplicate queries in same request
- Automatic cache key generation
- Built-in statistics
- Zero configuration

**When to use:**
- Repeated queries for same data
- Foreign key lookups
- Dropdown options
- Metadata queries

## Memory Management

### 1. Limit Result Sets

Always use `LIMIT` for large datasets:

```php
// Bad - loads all records
$stmt = $pdo->query("SELECT * FROM users");
$users = $stmt->fetchAll();

// Good - paginated results
$stmt = $pdo->prepare("SELECT * FROM users LIMIT :limit OFFSET :offset");
$stmt->execute(['limit' => 20, 'offset' => 0]);
$users = $stmt->fetchAll();
```

### 2. Use Generators for Large Datasets

For processing large datasets, use generators:

```php
function fetchRecords(PDO $pdo, string $table): \Generator
{
    $stmt = $pdo->query("SELECT * FROM {$table}");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        yield $row;
    }
}

// Process one record at a time
foreach (fetchRecords($pdo, 'users') as $user) {
    // Process user
}
```

### 3. Unset Large Variables

Free memory when done with large variables:

```php
$largeArray = $pdo->query("SELECT * FROM large_table")->fetchAll();

// Process data
processData($largeArray);

// Free memory
unset($largeArray);
```

### 4. Stream File Uploads

For large file uploads, use streaming:

```php
// Bad - loads entire file in memory
$content = file_get_contents($_FILES['file']['tmp_name']);

// Good - stream to destination
move_uploaded_file($_FILES['file']['tmp_name'], $destination);
```

### 5. Limit Cache Size

For persistent caches, implement size limits:

```php
class LimitedCache implements CacheStrategy
{
    private int $maxSize = 100;
    private array $cache = [];
    
    public function set(string $key, $value, int $ttl = 3600): void
    {
        if (count($this->cache) >= $this->maxSize) {
            // Remove oldest entry
            array_shift($this->cache);
        }
        
        $this->cache[$key] = $value;
    }
}
```

## Database Optimization

### 1. Use Prepared Statements

Always use prepared statements (already done in DynamicCRUD):

```php
// Good - prepared statement
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $id]);
```

### 2. Select Only Needed Columns

```php
// Bad - selects all columns
SELECT * FROM users

// Good - selects only needed columns
SELECT id, name, email FROM users
```

### 3. Add Indexes

Add indexes for frequently queried columns:

```sql
-- Foreign keys
CREATE INDEX idx_posts_user_id ON posts(user_id);

-- Search fields
CREATE INDEX idx_users_email ON users(email);

-- Composite indexes
CREATE INDEX idx_orders_user_status ON orders(user_id, status);
```

### 4. Use EXPLAIN

Analyze query performance:

```sql
EXPLAIN SELECT * FROM users WHERE email = 'test@example.com';
```

## Schema Caching

DynamicCRUD caches database schema to avoid repeated introspection:

```php
use Morpheus\Cache\FileCacheStrategy;

$cache = new FileCacheStrategy(__DIR__ . '/cache');
$crud = new Morpheus($pdo, 'users', $cache);

// Schema is cached for 1 hour by default
```

**Clear cache after schema changes:**

```php
$cache->clear();
// Or use CLI
php bin/morpheus clear:cache
```

## Profiling

### Memory Usage

Monitor memory usage:

```php
$memoryBefore = memory_get_usage();

// Your code here

$memoryAfter = memory_get_usage();
$memoryUsed = $memoryAfter - $memoryBefore;

echo "Memory used: " . round($memoryUsed / 1024 / 1024, 2) . " MB\n";
```

### Query Performance

Log slow queries:

```php
$start = microtime(true);

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$duration = microtime(true) - $start;

if ($duration > 0.1) { // 100ms threshold
    error_log("Slow query ({$duration}s): {$sql}");
}
```

### Peak Memory

Check peak memory usage:

```php
echo "Peak memory: " . round(memory_get_peak_usage() / 1024 / 1024, 2) . " MB\n";
```

## Best Practices

### 1. Pagination

Always paginate large lists:

```php
$crud = new Morpheus($pdo, 'users');
echo $crud->renderList(['perPage' => 20, 'page' => 1]);
```

### 2. Lazy Loading

Load related data only when needed:

```php
// Bad - loads all related data upfront
$user = $crud->getUser($id);
$user['posts'] = $crud->getUserPosts($id);
$user['comments'] = $crud->getUserComments($id);

// Good - load on demand
$user = $crud->getUser($id);
if ($needPosts) {
    $user['posts'] = $crud->getUserPosts($id);
}
```

### 3. Batch Operations

Process records in batches:

```php
$batchSize = 100;
$offset = 0;

while (true) {
    $stmt = $pdo->prepare("SELECT * FROM users LIMIT :limit OFFSET :offset");
    $stmt->execute(['limit' => $batchSize, 'offset' => $offset]);
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        break;
    }
    
    foreach ($users as $user) {
        // Process user
    }
    
    $offset += $batchSize;
}
```

### 4. Connection Pooling

Reuse database connections:

```php
// Singleton pattern for PDO
class Database
{
    private static ?PDO $instance = null;
    
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new PDO($dsn, $user, $pass);
        }
        
        return self::$instance;
    }
}

$pdo = Database::getInstance();
```

## Benchmarking

### Simple Benchmark

```php
function benchmark(callable $fn, int $iterations = 1000): float
{
    $start = microtime(true);
    
    for ($i = 0; $i < $iterations; $i++) {
        $fn();
    }
    
    return microtime(true) - $start;
}

$time = benchmark(function() use ($crud) {
    $crud->renderForm();
});

echo "Average time: " . round($time / 1000 * 1000, 2) . " ms\n";
```

### Compare Approaches

```php
// Approach 1
$time1 = benchmark(function() {
    // Without cache
});

// Approach 2
$time2 = benchmark(function() {
    // With cache
});

$improvement = round(($time1 - $time2) / $time1 * 100, 2);
echo "Performance improvement: {$improvement}%\n";
```

## Monitoring

### Production Monitoring

```php
// Log performance metrics
function logMetrics(string $operation, float $duration, int $memory): void
{
    $data = [
        'operation' => $operation,
        'duration' => $duration,
        'memory' => $memory,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    file_put_contents(
        'metrics.log',
        json_encode($data) . "\n",
        FILE_APPEND
    );
}

$start = microtime(true);
$memBefore = memory_get_usage();

$crud->handleSubmission();

$duration = microtime(true) - $start;
$memory = memory_get_usage() - $memBefore;

logMetrics('handleSubmission', $duration, $memory);
```

## Configuration

### PHP Settings

Optimize PHP configuration:

```ini
; Increase memory limit for large operations
memory_limit = 256M

; Enable OPcache
opcache.enable = 1
opcache.memory_consumption = 128
opcache.max_accelerated_files = 10000

; Disable unnecessary extensions
; extension=xdebug (only in development)
```

### MySQL Settings

Optimize MySQL configuration:

```ini
[mysqld]
# Query cache (MySQL 5.7)
query_cache_type = 1
query_cache_size = 64M

# InnoDB buffer pool
innodb_buffer_pool_size = 1G

# Connection pool
max_connections = 200
```

## Troubleshooting

### Memory Leaks

Identify memory leaks:

```php
$baseline = memory_get_usage();

for ($i = 0; $i < 100; $i++) {
    $crud->renderForm();
    
    $current = memory_get_usage();
    $diff = $current - $baseline;
    
    echo "Iteration {$i}: {$diff} bytes\n";
}
```

### Slow Queries

Enable query logging:

```php
$pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, [LoggingStatement::class]);

class LoggingStatement extends PDOStatement
{
    public function execute($params = null): bool
    {
        $start = microtime(true);
        $result = parent::execute($params);
        $duration = microtime(true) - $start;
        
        if ($duration > 0.1) {
            error_log("Slow query: {$this->queryString} ({$duration}s)");
        }
        
        return $result;
    }
}
```

## Resources

- [PHP Performance Tips](https://www.php.net/manual/en/features.performance.php)
- [MySQL Performance Tuning](https://dev.mysql.com/doc/refman/8.0/en/optimization.html)
- [PostgreSQL Performance Tips](https://wiki.postgresql.org/wiki/Performance_Optimization)

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
