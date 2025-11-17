# Content Types System

**Version:** 4.0+  
**Status:** Stable

## Overview

The Content Types system allows you to install pre-configured database schemas with full metadata, creating complete applications in seconds. Think of it as "WordPress post types on steroids" - each content type is a complete, production-ready application.

## Quick Start

```php
use Morpheus\ContentTypes\ContentTypeManager;

$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');
$manager = new ContentTypeManager($pdo);

// Install blog content type (5 tables with metadata)
$manager->install('blog');

// Check if installed
if ($manager->isInstalled('blog')) {
    echo "Blog is ready!";
}

// Uninstall when done
$manager->uninstall('blog');
```

## Available Content Types

### Blog (WordPress Alternative)

Complete blogging platform with posts, categories, tags, and comments.

**Tables Created:**
- `posts` - Blog posts with title, content, excerpt, status
- `categories` - Post categories
- `tags` - Post tags
- `post_tags` - Many-to-many pivot table
- `comments` - Post comments (optional)

**Features:**
- ✅ SEO-optimized (meta tags, Open Graph, Schema.org)
- ✅ RSS feed generation
- ✅ XML sitemap
- ✅ Clean URLs (`/blog/my-post`)
- ✅ Search functionality
- ✅ Category and tag archives
- ✅ Draft/published status
- ✅ Featured images
- ✅ Automatic slug generation
- ✅ Timestamps (created_at, updated_at)

**Installation:**
```php
$manager->install('blog');
```

## Creating Custom Content Types

### 1. Implement ContentType Interface

```php
namespace Morpheus\ContentTypes;

class PortfolioContentType implements ContentType
{
    private string $prefix;
    
    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;
    }
    
    public function getName(): string
    {
        return 'portfolio';
    }
    
    public function getDescription(): string
    {
        return 'Portfolio with projects, galleries, and testimonials';
    }
    
    public function install(\PDO $pdo): bool
    {
        // Create tables with metadata
        $pdo->exec("
            CREATE TABLE {$this->prefix}projects (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) UNIQUE,
                description TEXT,
                image VARCHAR(255),
                url VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) COMMENT '{\"display_name\":\"Projects\",\"icon\":\"💼\"}'
        ");
        
        return true;
    }
    
    public function uninstall(\PDO $pdo): bool
    {
        $pdo->exec("DROP TABLE IF EXISTS {$this->prefix}projects");
        return true;
    }
    
    public function isInstalled(\PDO $pdo): bool
    {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$this->prefix}projects'");
        return $stmt->rowCount() > 0;
    }
}
```

### 2. Register Content Type

```php
$manager = new ContentTypeManager($pdo);
$manager->register('portfolio', new PortfolioContentType());
$manager->install('portfolio');
```

## Table Prefixes

Use prefixes to isolate content types or create multiple instances:

```php
// Blog for example 24
$blog = new BlogContentType('24_');
$blog->install($pdo);

// Blog for example 25
$blog2 = new BlogContentType('25_');
$blog2->install($pdo);

// Production blog (no prefix)
$prodBlog = new BlogContentType();
$prodBlog->install($pdo);
```

## ContentTypeManager API

### install(string $name): bool

Installs a content type by name.

```php
$result = $manager->install('blog');
if ($result) {
    echo "Blog installed successfully!";
}
```

### uninstall(string $name): bool

Uninstalls a content type, removing all tables.

```php
$manager->uninstall('blog');
```

### isInstalled(string $name): bool

Checks if a content type is installed.

```php
if ($manager->isInstalled('blog')) {
    echo "Blog is ready to use";
}
```

### getAvailable(): array

Returns all registered content types.

```php
$available = $manager->getAvailable();
// ['blog' => BlogContentType, ...]
```

### getInstalled(): array

Returns installed content types with status.

```php
$installed = $manager->getInstalled();
// ['blog' => ['installed' => true, 'description' => '...']]
```

### register(string $name, ContentType $type): void

Registers a custom content type.

```php
$manager->register('portfolio', new PortfolioContentType());
```

## Best Practices

### 1. Always Use Prefixes in Examples

```php
// ✅ Good - Isolated
$blog = new BlogContentType('example_');

// ❌ Bad - Conflicts with other examples
$blog = new BlogContentType();
```

### 2. Disable Foreign Key Checks

```php
public function install(\PDO $pdo): bool
{
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Create tables...
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    return true;
}
```

### 3. Include Full Metadata

```php
$tableComment = json_encode([
    'display_name' => 'Blog Posts',
    'icon' => '📝',
    'list_view' => [
        'searchable' => ['title', 'content'],
        'per_page' => 20
    ],
    'behaviors' => [
        'timestamps' => true,
        'sluggable' => ['source' => 'title', 'target' => 'slug']
    ]
]);

$pdo->exec("ALTER TABLE posts COMMENT = '{$tableComment}'");
```

### 4. Provide Sample Data

```php
public function install(\PDO $pdo): bool
{
    // Create tables...
    
    // Insert sample data
    $pdo->exec("
        INSERT INTO {$this->prefix}posts (title, slug, content, status) VALUES
        ('Welcome Post', 'welcome', '<p>Welcome to your blog!</p>', 'published')
    ");
    
    return true;
}
```

## Integration with DynamicCRUD

Content types work seamlessly with DynamicCRUD:

```php
// Install blog
$manager->install('blog');

// Use with DynamicCRUD
$crud = new Morpheus($pdo, 'posts');
echo $crud->renderForm();
echo $crud->renderList();
```

## Frontend Rendering

Content types can include frontend rendering:

```php
use Morpheus\Frontend\FrontendRenderer;
use Morpheus\Frontend\SEOManager;

// Install blog
$manager->install('blog');

// Render frontend
$renderer = new FrontendRenderer($pdo);
echo $renderer->renderHome();
echo $renderer->renderSingle('my-post');

// Generate SEO
$seo = new SEOManager($pdo, 'https://example.com', 'My Blog');
file_put_contents('sitemap.xml', $seo->generateSitemap());
file_put_contents('feed.xml', $seo->generateRSS());
```

## Roadmap

**v4.1 - More Content Types:**
- Portfolio (projects, galleries, testimonials)
- E-commerce (products, orders, customers)
- Directory (listings, reviews, ratings)
- Documentation (docs, categories, search)

**v4.2 - Visual Builder:**
- Drag-and-drop content type creator
- No-code table designer
- Metadata editor
- Sample data generator

**v4.3 - Marketplace:**
- Share custom content types
- Download community content types
- Premium content types
- One-click installation

## Examples

See [examples/24-blog-cms/](../examples/24-blog-cms/) for a complete working example.

## Related Documentation

- [Frontend Rendering](FRONTEND_RENDERING.md)
- [SEO Manager](SEO.md)
- [Table Metadata](TABLE_METADATA.md)
- [Universal CMS Vision](../UNIVERSAL_CMS.md)
