# Database Routing

**Status:** Planned for v4.1  
**Philosophy:** Configuration in database, not code

---

## Concept

Store routing configuration in a database table instead of code files. This aligns with DynamicCRUD's philosophy: **everything configurable through database metadata**.

## Why Database Routing?

### 1. Consistency with DynamicCRUD
- Schema metadata → Database comments
- Table metadata → Database comments
- Theme configuration → `_themes` table
- **Routes → `_routes` table** ✨

### 2. Dynamic Management
- Create/edit routes from admin panel
- No code deployments for route changes
- A/B testing: activate/deactivate routes
- Multi-tenant: each tenant has custom routes

### 3. Powerful Features
- Route analytics and usage tracking
- Permission-based routing
- Middleware chains
- Route versioning
- Priority ordering

---

## Database Schema

```sql
CREATE TABLE _routes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    path VARCHAR(255) NOT NULL,
    method ENUM('GET', 'POST', 'PUT', 'DELETE', 'PATCH') DEFAULT 'GET',
    handler VARCHAR(255) NOT NULL,
    middleware JSON,
    params JSON,
    priority INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_path (path),
    INDEX idx_active (active),
    INDEX idx_priority (priority)
) COMMENT '{"display_name":"Routes","icon":"🛤️"}';
```

---

## Handler Format

Routes use a simple string format to specify handlers:

```
Type:Target:Action
```

### Examples

**Content Types:**
```
ContentType:Blog:single
ContentType:Blog:archive
ContentType:Blog:category
```

**API Endpoints:**
```
API:Users:list
API:Users:show
API:Users:update
```

**Static Pages:**
```
Page:Static:about
Page:Contact:form
```

**Custom Controllers:**
```
Controller:ProductController:show
Controller:OrderController:checkout
```

---

## Route Examples

### Blog Routes
```sql
INSERT INTO _routes (path, handler, priority) VALUES 
('/blog', 'ContentType:Blog:archive', 10),
('/blog/:slug', 'ContentType:Blog:single', 20),
('/category/:slug', 'ContentType:Blog:category', 20),
('/tag/:slug', 'ContentType:Blog:tag', 20),
('/search', 'ContentType:Blog:search', 10);
```

### API Routes with Middleware
```sql
INSERT INTO _routes (path, method, handler, middleware) VALUES 
('/api/users', 'GET', 'API:Users:list', '["auth"]'),
('/api/users/:id', 'GET', 'API:Users:show', '["auth"]'),
('/api/users/:id', 'PUT', 'API:Users:update', '["auth", "owner"]'),
('/api/users/:id', 'DELETE', 'API:Users:delete', '["auth", "admin"]');
```

### Static Pages
```sql
INSERT INTO _routes (path, handler) VALUES 
('/', 'Page:Home:index'),
('/about', 'Page:Static:about'),
('/contact', 'Page:Contact:form'),
('/pricing', 'Page:Static:pricing');
```

### Multi-Tenant Routes
```sql
-- Tenant-specific routes
INSERT INTO _routes (path, handler, params) VALUES 
('/:tenant/dashboard', 'Tenant:Dashboard:index', '{"tenant":"string"}'),
('/:tenant/settings', 'Tenant:Settings:index', '{"tenant":"string"}');
```

---

## Route Parameters

Routes support dynamic parameters with type validation:

```sql
-- Parameter types
INSERT INTO _routes (path, handler, params) VALUES 
('/users/:id', 'API:Users:show', '{"id":"int"}'),
('/blog/:slug', 'ContentType:Blog:single', '{"slug":"string"}'),
('/archive/:year/:month', 'ContentType:Blog:archive', '{"year":"int","month":"int"}');
```

**Supported Types:**
- `int` - Integer validation
- `string` - String (default)
- `slug` - Alphanumeric + hyphens
- `uuid` - UUID format
- `date` - Date format (YYYY-MM-DD)

---

## Middleware

Middleware chains execute before route handlers:

```sql
INSERT INTO _routes (path, handler, middleware) VALUES 
('/admin/users', 'Admin:Users:index', '["auth", "admin"]'),
('/api/posts', 'API:Posts:create', '["auth", "csrf", "rate_limit"]');
```

**Built-in Middleware:**
- `auth` - Require authentication
- `guest` - Require guest (not authenticated)
- `admin` - Require admin role
- `owner` - Require resource ownership
- `csrf` - CSRF token validation
- `rate_limit` - Rate limiting
- `cors` - CORS headers

---

## Priority System

Routes are matched by priority (higher = first):

```sql
-- Specific routes (high priority)
INSERT INTO _routes (path, handler, priority) VALUES 
('/blog/featured', 'ContentType:Blog:featured', 100);

-- Dynamic routes (lower priority)
INSERT INTO _routes (path, handler, priority) VALUES 
('/blog/:slug', 'ContentType:Blog:single', 50);

-- Catch-all (lowest priority)
INSERT INTO _routes (path, handler, priority) VALUES 
('/:slug', 'Page:Static:show', 10);
```

---

## PHP Implementation

### DatabaseRouter Class

```php
namespace Morpheus\Routing;

class DatabaseRouter
{
    private PDO $pdo;
    private array $middleware = [];
    
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    public function match(string $path, string $method = 'GET'): ?Route
    {
        // 1. Get active routes ordered by priority
        $routes = $this->getActiveRoutes($method);
        
        // 2. Match path against routes
        foreach ($routes as $routeData) {
            if ($route = $this->matchRoute($path, $routeData)) {
                return $route;
            }
        }
        
        return null;
    }
    
    public function dispatch(Route $route): string
    {
        // 1. Execute middleware chain
        foreach ($route->middleware as $middleware) {
            $this->executeMiddleware($middleware, $route);
        }
        
        // 2. Parse handler
        [$type, $target, $action] = explode(':', $route->handler);
        
        // 3. Execute handler
        return $this->executeHandler($type, $target, $action, $route->params);
    }
    
    private function matchRoute(string $path, array $routeData): ?Route
    {
        // Convert route pattern to regex
        $pattern = $this->patternToRegex($routeData['path']);
        
        if (preg_match($pattern, $path, $matches)) {
            return new Route(
                path: $routeData['path'],
                handler: $routeData['handler'],
                params: $this->extractParams($matches, $routeData['params']),
                middleware: json_decode($routeData['middleware'] ?? '[]', true)
            );
        }
        
        return null;
    }
}
```

### Route Value Object

```php
namespace Morpheus\Routing;

class Route
{
    public function __construct(
        public string $path,
        public string $handler,
        public array $params = [],
        public array $middleware = []
    ) {}
}
```

### Usage Example

```php
use Morpheus\Routing\DatabaseRouter;

$router = new DatabaseRouter($pdo);

// Match current request
$route = $router->match($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

if ($route) {
    echo $router->dispatch($route);
} else {
    http_response_code(404);
    echo "404 Not Found";
}
```

---

## Admin Panel Integration

### Route Manager UI

```php
use Morpheus\Admin\AdminPanel;

$admin = new AdminPanel($pdo);

// Add routes table to admin
$admin->addTable('_routes', [
    'icon' => '🛤️',
    'label' => 'Routes',
    'list_view' => [
        'columns' => ['path', 'method', 'handler', 'active'],
        'searchable' => ['path', 'handler'],
        'filters' => ['method', 'active']
    ]
]);
```

### Route Testing Tool

```php
// Test route matching
$tester = new RouteTester($pdo);
$result = $tester->test('/blog/my-post', 'GET');

echo "Matched Route: {$result->path}\n";
echo "Handler: {$result->handler}\n";
echo "Params: " . json_encode($result->params) . "\n";
```

---

## CLI Commands

```bash
# List all routes
php bin/morpheus route:list

# Add route
php bin/morpheus route:add /blog/:slug ContentType:Blog:single

# Test route
php bin/morpheus route:test /blog/my-post

# Clear route cache
php bin/morpheus route:cache:clear

# Generate routes from content types
php bin/morpheus route:generate blog
```

---

## Benefits vs Code-Based Routing

| Feature | Code Routing | Database Routing |
|---------|-------------|------------------|
| **Deployment** | Requires code deploy | Instant via admin panel |
| **Multi-tenant** | Complex configuration | Native support |
| **A/B Testing** | Code changes | Toggle active flag |
| **Analytics** | Custom implementation | Built-in tracking |
| **Versioning** | Git history | Database history |
| **Non-technical** | Requires developer | Admin can manage |
| **Dynamic** | Static at runtime | Fully dynamic |

---

## Performance Considerations

### Route Caching

```php
class DatabaseRouter
{
    private ?array $routeCache = null;
    
    private function getActiveRoutes(string $method): array
    {
        if ($this->routeCache === null) {
            $stmt = $this->pdo->query("
                SELECT * FROM _routes 
                WHERE active = 1 AND (method = '$method' OR method = 'ANY')
                ORDER BY priority DESC
            ");
            $this->routeCache = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $this->routeCache;
    }
}
```

### Compiled Routes

```bash
# Compile routes to PHP file for production
php bin/morpheus route:compile

# Generates: cache/routes.php
# Contains: Pre-compiled route patterns and handlers
```

---

## Migration Path

### Phase 1: Hybrid Approach (v4.1)
- Keep existing FrontendRouter (code-based)
- Add DatabaseRouter as optional
- Allow mixing both approaches

### Phase 2: Database-First (v4.2)
- DatabaseRouter becomes default
- Code-based routing deprecated
- Migration tool: code → database

### Phase 3: Database-Only (v5.0)
- Remove code-based routing
- Full database routing
- Advanced features (versioning, analytics)

---

## Future Enhancements

### Route Groups
```sql
INSERT INTO _route_groups (name, prefix, middleware) VALUES 
('admin', '/admin', '["auth", "admin"]');

INSERT INTO _routes (path, handler, group_id) VALUES 
('/users', 'Admin:Users:index', 1),
('/posts', 'Admin:Posts:index', 1);
-- Results in: /admin/users, /admin/posts
```

### Route Analytics
```sql
CREATE TABLE _route_analytics (
    route_id INT,
    hits INT DEFAULT 0,
    avg_response_time DECIMAL(10,2),
    last_accessed TIMESTAMP
);
```

### Route Versioning
```sql
ALTER TABLE _routes ADD COLUMN version VARCHAR(10) DEFAULT 'v1';

INSERT INTO _routes (path, handler, version) VALUES 
('/api/users', 'API:Users:list', 'v1'),
('/api/users', 'API:UsersV2:list', 'v2');
```

---

## Conclusion

Database routing is a natural evolution of DynamicCRUD's philosophy:

✅ **Configuration in database, not code**  
✅ **Dynamic and flexible**  
✅ **Admin-friendly**  
✅ **Multi-tenant ready**  
✅ **Performance optimized**

**Target Release:** v4.1 (Q4 2025)
