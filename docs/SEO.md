# SEO Manager

**Version:** 4.0+  
**Status:** Stable

## Overview

SEOManager provides comprehensive SEO functionality out of the box: meta tags, Open Graph, Twitter Cards, Schema.org structured data, XML sitemaps, and RSS feeds. No plugins required.

## Quick Start

```php
use Morpheus\Frontend\SEOManager;

$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');
$seo = new SEOManager($pdo, 'https://example.com', 'My Blog');

// Generate meta tags for a post
$post = ['title' => 'My Post', 'slug' => 'my-post', 'excerpt' => 'Post excerpt'];
echo $seo->generateMetaTags($post);

// Generate sitemap
file_put_contents('sitemap.xml', $seo->generateSitemap());

// Generate RSS feed
file_put_contents('feed.xml', $seo->generateRSS());
```

## Constructor

```php
public function __construct(
    \PDO $pdo,
    string $baseUrl,
    string $siteName,
    string $prefix = ''
)
```

**Parameters:**
- `$pdo` - Database connection
- `$baseUrl` - Site URL (e.g., `https://example.com`)
- `$siteName` - Site name for meta tags
- `$prefix` - Table prefix (optional)

## Meta Tags

### generateMetaTags(array $post): string

Generates complete meta tags for a post.

```php
$post = [
    'title' => 'My First Post',
    'slug' => 'my-first-post',
    'excerpt' => 'This is my first blog post',
    'featured_image' => 'image.jpg',
    'published_at' => '2025-01-15 10:00:00',
    'updated_at' => '2025-01-16 12:00:00'
];

echo $seo->generateMetaTags($post);
```

**Output:**
```html
<title>My First Post - My Blog</title>
<meta name="description" content="This is my first blog post">
<link rel="canonical" href="https://example.com/blog/my-first-post">

<!-- Open Graph -->
<meta property="og:type" content="article">
<meta property="og:title" content="My First Post">
<meta property="og:description" content="This is my first blog post">
<meta property="og:url" content="https://example.com/blog/my-first-post">
<meta property="og:image" content="https://example.com/uploads/image.jpg">
<meta property="og:site_name" content="My Blog">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="My First Post">
<meta name="twitter:description" content="This is my first blog post">
<meta name="twitter:image" content="https://example.com/uploads/image.jpg">
```

## Open Graph Tags

Automatically generated for Facebook sharing:

```php
$seo->generateMetaTags($post);
```

**Includes:**
- `og:type` - "article"
- `og:title` - Post title
- `og:description` - Post excerpt
- `og:url` - Canonical URL
- `og:image` - Featured image
- `og:site_name` - Site name

## Twitter Cards

Automatically generated for Twitter sharing:

```php
$seo->generateMetaTags($post);
```

**Includes:**
- `twitter:card` - "summary_large_image"
- `twitter:title` - Post title
- `twitter:description` - Post excerpt
- `twitter:image` - Featured image

## Schema.org Structured Data

### generateSchemaOrg(array $post): string

Generates JSON-LD structured data for search engines.

```php
echo $seo->generateSchemaOrg($post);
```

**Output:**
```html
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    "headline": "My First Post",
    "description": "This is my first blog post",
    "url": "https://example.com/blog/my-first-post",
    "datePublished": "2025-01-15T10:00:00+00:00",
    "dateModified": "2025-01-16T12:00:00+00:00",
    "author": {
        "@type": "Person",
        "name": "My Blog"
    },
    "publisher": {
        "@type": "Organization",
        "name": "My Blog",
        "url": "https://example.com"
    },
    "image": "https://example.com/uploads/image.jpg"
}
</script>
```

## XML Sitemap

### generateSitemap(): string

Generates XML sitemap with all published posts, categories, and tags.

```php
$xml = $seo->generateSitemap();
file_put_contents('sitemap.xml', $xml);
```

**Output:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://example.com</loc>
        <lastmod>2025-01-15</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://example.com/blog/my-first-post</loc>
        <lastmod>2025-01-15</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
    </url>
    <url>
        <loc>https://example.com/blog/category/technology</loc>
        <changefreq>weekly</changefreq>
        <priority>0.6</priority>
    </url>
</urlset>
```

**Includes:**
- Homepage (priority 1.0)
- All published posts (priority 0.8)
- All categories (priority 0.6)
- All tags (priority 0.6)

### Submit to Search Engines

```bash
# Google Search Console
curl https://www.google.com/ping?sitemap=https://example.com/sitemap.xml

# Bing Webmaster Tools
curl https://www.bing.com/ping?sitemap=https://example.com/sitemap.xml
```

## RSS Feed

### generateRSS(int $limit = 20): string

Generates RSS 2.0 feed with recent posts.

```php
$xml = $seo->generateRSS(10); // Last 10 posts
file_put_contents('feed.xml', $xml);
```

**Output:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>My Blog</title>
        <link>https://example.com</link>
        <description>Latest posts from My Blog</description>
        <language>en</language>
        <lastBuildDate>Wed, 15 Jan 2025 10:00:00 +0000</lastBuildDate>
        
        <item>
            <title>My First Post</title>
            <link>https://example.com/blog/my-first-post</link>
            <description>This is my first blog post</description>
            <pubDate>Wed, 15 Jan 2025 10:00:00 +0000</pubDate>
            <guid>https://example.com/blog/my-first-post</guid>
        </item>
    </channel>
</rss>
```

## Integration with Frontend

```php
use Morpheus\Frontend\FrontendRenderer;
use Morpheus\Frontend\SEOManager;

$seo = new SEOManager($pdo, 'https://example.com', 'My Blog');
$renderer = new FrontendRenderer($pdo, 'blog', null, $seo);

// Meta tags automatically included
echo $renderer->renderSingle('my-post');
```

## Automatic Generation

### Cron Job for Sitemap/RSS

```bash
# crontab -e
0 * * * * php /path/to/generate-seo.php
```

```php
<?php
// generate-seo.php
require 'vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=mydb', 'user', 'pass');
$seo = new SEOManager($pdo, 'https://example.com', 'My Blog');

file_put_contents(__DIR__ . '/sitemap.xml', $seo->generateSitemap());
file_put_contents(__DIR__ . '/feed.xml', $seo->generateRSS());

echo "SEO files generated!\n";
```

## Best Practices

### 1. Use Descriptive Excerpts

```php
// ✅ Good
$post['excerpt'] = 'Learn how to build a blog with DynamicCRUD in 5 minutes';

// ❌ Bad
$post['excerpt'] = 'Click here to read more';
```

### 2. Optimize Featured Images

```php
// Resize images to 1200x630 for Open Graph
// Use descriptive filenames
$post['featured_image'] = 'how-to-build-blog-dynamiccrud.jpg';
```

### 3. Keep Titles Under 60 Characters

```php
// ✅ Good (58 chars)
$post['title'] = 'How to Build a Blog with DynamicCRUD in 5 Minutes';

// ❌ Bad (too long, will be truncated)
$post['title'] = 'The Complete Step-by-Step Guide to Building a Professional Blog...';
```

### 4. Update Sitemap After Changes

```php
// After creating/updating posts
$seo->generateSitemap();
file_put_contents('sitemap.xml', $seo->generateSitemap());
```

## Performance

### Cache Generated Files

```php
// Generate once, serve many times
if (!file_exists('sitemap.xml') || filemtime('sitemap.xml') < time() - 3600) {
    file_put_contents('sitemap.xml', $seo->generateSitemap());
}

header('Content-Type: application/xml');
readfile('sitemap.xml');
```

### CDN for Images

```php
// Use CDN for featured images
$cdnUrl = 'https://cdn.example.com';
$post['featured_image'] = $cdnUrl . '/uploads/' . $post['featured_image'];
```

## Testing

### Validate Sitemap

```bash
# Google Sitemap Validator
curl -X POST https://www.google.com/webmasters/tools/ping?sitemap=https://example.com/sitemap.xml
```

### Test Open Graph

- [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
- [Twitter Card Validator](https://cards-dev.twitter.com/validator)

### Test Schema.org

- [Google Rich Results Test](https://search.google.com/test/rich-results)
- [Schema.org Validator](https://validator.schema.org/)

## Examples

See [examples/24-blog-cms/](../examples/24-blog-cms/) for a complete working example.

## Related Documentation

- [Frontend Rendering](FRONTEND_RENDERING.md)
- [Content Types](CONTENT_TYPES.md)
- [Universal CMS Vision](../UNIVERSAL_CMS.md)
