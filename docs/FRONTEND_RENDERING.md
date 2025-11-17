# Frontend Rendering System

**Version:** 4.0+  
**Status:** Stable

## Overview

The Frontend Rendering system transforms DynamicCRUD from a backend-only tool into a complete CMS with public-facing pages. It provides clean URLs, SEO optimization, and beautiful templates out of the box.

## Quick Start

```php
use Morpheus\Frontend\FrontendRenderer;
use Morpheus\Frontend\FrontendRouter;

$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');

// Route the request
$router = new FrontendRouter();
$route = $router->match($_SERVER['REQUEST_URI']);

// Render the page
$renderer = new FrontendRenderer($pdo);

if ($route) {
    switch ($route->handler) {
        case 'home':
            echo $renderer->renderHome();
            break;
        case 'blog.single':
            echo $renderer->renderSingle($route->params['slug']);
            break;
        case 'blog.archive':
            echo $renderer->renderArchive();
            break;
    }
} else {
    echo $renderer->render404();
}
```

## FrontendRouter

Routes URLs to handlers using pattern matching.

### Default Routes

```php
'/' => 'home'
'/blog' => 'blog.archive'
'/blog/{slug}' => 'blog.single'
'/blog/category/{slug}' => 'blog.category'
'/blog/tag/{slug}' => 'blog.tag'
'/blog/page/{page}' => 'blog.archive'
'/search' => 'search'
'/{slug}' => 'page.single'
```

### Custom Routes

```php
$router = new FrontendRouter();

// Add custom route
$router->addRoute('/products/{id}', 'product.single');
$router->addRoute('/products/category/{slug}', 'product.category');

// Match URL
$route = $router->match('/products/123');
// $route->handler = 'product.single'
// $route->params = ['id' => '123']
```

### Route Object

```php
class Route {
    public string $pattern;    // '/blog/{slug}'
    public string $handler;    // 'blog.single'
    public array $params;      // ['slug' => 'my-post']
}
```

## FrontendRenderer

Renders public-facing pages with clean HTML.

### Constructor

```php
public function __construct(
    \PDO $pdo,
    string $contentType = 'blog',
    ?\DynamicCRUD\Template\TemplateEngine $templateEngine = null,
    ?\DynamicCRUD\Frontend\SEOManager $seo = null,
    string $prefix = ''
)
```

### Rendering Methods

#### renderHome(int $page = 1): string

Renders homepage with recent posts.

```php
$html = $renderer->renderHome();
$html = $renderer->renderHome(2); // Page 2
```

#### renderSingle(string $slug): string

Renders single post by slug.

```php
$html = $renderer->renderSingle('my-first-post');
```

#### renderArchive(int $page = 1): string

Renders post archive with pagination.

```php
$html = $renderer->renderArchive();
$html = $renderer->renderArchive(2); // Page 2
```

#### renderCategory(string $slug, int $page = 1): string

Renders category archive.

```php
$html = $renderer->renderCategory('technology');
```

#### renderTag(string $slug, int $page = 1): string

Renders tag archive.

```php
$html = $renderer->renderTag('php');
```

#### renderSearch(string $query, int $page = 1): string

Renders search results.

```php
$html = $renderer->renderSearch('tutorial');
```

#### render404(): string

Renders 404 page.

```php
$html = $renderer->render404();
```

## Template Integration

Use custom templates instead of default HTML:

```php
use Morpheus\Template\BladeTemplate;

$engine = new BladeTemplate(__DIR__ . '/templates', __DIR__ . '/cache');
$renderer = new FrontendRenderer($pdo, 'blog', $engine);

echo $renderer->renderHome();
```

### Template Variables

**Home/Archive:**
```php
[
    'posts' => [...],
    'page' => 1,
    'total_pages' => 5,
    'has_next' => true,
    'has_prev' => false
]
```

**Single Post:**
```php
[
    'post' => [
        'id' => 1,
        'title' => 'My Post',
        'slug' => 'my-post',
        'content' => '<p>...</p>',
        'excerpt' => '...',
        'category' => 'Technology',
        'tags' => ['PHP', 'MySQL'],
        'published_at' => '2025-01-15'
    ]
]
```

**Category/Tag:**
```php
[
    'category' => 'Technology', // or 'tag'
    'posts' => [...],
    'page' => 1,
    'total_pages' => 3
]
```

## Clean URLs

### Apache (.htaccess)

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### PHP Built-in Server

```php
// router.php
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

if (file_exists(__DIR__ . $_SERVER['REQUEST_URI'])) {
    return false;
}

require __DIR__ . '/index.php';
```

```bash
php -S localhost:8000 router.php
```

## Complete Example

```php
<?php
// index.php
require 'vendor/autoload.php';

use Morpheus\Frontend\FrontendRouter;
use Morpheus\Frontend\FrontendRenderer;
use Morpheus\Frontend\SEOManager;

$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');

// Setup
$router = new FrontendRouter();
$seo = new SEOManager($pdo, 'https://example.com', 'My Blog');
$renderer = new FrontendRenderer($pdo, 'blog', null, $seo);

// Route
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = $router->match($path);

// Render
if (!$route) {
    http_response_code(404);
    echo $renderer->render404();
    exit;
}

switch ($route->handler) {
    case 'home':
        echo $renderer->renderHome();
        break;
        
    case 'blog.single':
        echo $renderer->renderSingle($route->params['slug']);
        break;
        
    case 'blog.archive':
        $page = $route->params['page'] ?? 1;
        echo $renderer->renderArchive($page);
        break;
        
    case 'blog.category':
        echo $renderer->renderCategory($route->params['slug']);
        break;
        
    case 'blog.tag':
        echo $renderer->renderTag($route->params['slug']);
        break;
        
    case 'search':
        $query = $_GET['q'] ?? '';
        echo $renderer->renderSearch($query);
        break;
        
    default:
        http_response_code(404);
        echo $renderer->render404();
}
```

## Customization

### Custom Post Query

```php
class MyRenderer extends FrontendRenderer
{
    protected function getPostsQuery(int $page, int $perPage): array
    {
        // Custom query logic
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM posts 
                WHERE status = 'published' 
                AND featured = 1
                ORDER BY published_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";
        
        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
}
```

### Custom Template

```php
// Override renderSimpleHTML for custom design
class MyRenderer extends FrontendRenderer
{
    protected function renderSimpleHTML(array $data, string $type): string
    {
        // Your custom HTML
        return "<html>...</html>";
    }
}
```

## Performance

### Caching

```php
// Cache rendered pages
$cacheKey = "page_{$slug}";
$html = $cache->get($cacheKey);

if (!$html) {
    $html = $renderer->renderSingle($slug);
    $cache->set($cacheKey, $html, 3600); // 1 hour
}

echo $html;
```

### Database Optimization

```php
// Add indexes for better performance
ALTER TABLE posts ADD INDEX idx_status_published (status, published_at);
ALTER TABLE posts ADD INDEX idx_slug (slug);
ALTER TABLE categories ADD INDEX idx_slug (slug);
ALTER TABLE tags ADD INDEX idx_slug (slug);
```

## SEO Integration

```php
$seo = new SEOManager($pdo, 'https://example.com', 'My Blog');
$renderer = new FrontendRenderer($pdo, 'blog', null, $seo);

// Meta tags automatically included in rendered HTML
echo $renderer->renderSingle('my-post');
// Includes: title, description, canonical, Open Graph, Twitter Cards, Schema.org
```

## Examples

See [examples/24-blog-cms/](../examples/24-blog-cms/) for a complete working example.

## Related Documentation

- [Content Types](CONTENT_TYPES.md)
- [SEO Manager](SEO.md)
- [Template System](TEMPLATES.md)
- [Universal CMS Vision](../UNIVERSAL_CMS.md)
