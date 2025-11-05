<?php
/**
 * Setup Blog CMS
 * 
 * Installs blog content type and creates sample data
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\ContentTypes\ContentTypeManager;
use DynamicCRUD\Frontend\SEOManager;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "🚀 Installing Blog CMS...\n\n";

// Clean up existing data
echo "🧹 Cleaning up existing data...\n";
try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS 24_comments");
    $pdo->exec("DROP TABLE IF EXISTS 24_post_tags");
    $pdo->exec("DROP TABLE IF EXISTS 24_tags");
    $pdo->exec("DROP TABLE IF EXISTS 24_posts");
    $pdo->exec("DROP TABLE IF EXISTS 24_categories");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "✅ Cleanup complete\n\n";
} catch (Exception $e) {
    echo "⚠️  Cleanup warning: " . $e->getMessage() . "\n\n";
}

// Install blog content type with prefix
use DynamicCRUD\ContentTypes\BlogContentType;
$blog = new BlogContentType('24_');
echo "📦 Installing blog content type...\n";
$blog->install($pdo);
echo "✅ Blog content type installed!\n\n";

// Create sample data
echo "📝 Creating sample data...\n";

// Categories
$pdo->exec("INSERT INTO 24_categories (name, slug, description) VALUES 
    ('Technology', 'technology', 'Tech news and tutorials'),
    ('Lifestyle', 'lifestyle', 'Life, travel, and culture'),
    ('Business', 'business', 'Business and entrepreneurship')
");
echo "✅ Created 3 categories\n";

// Tags
$pdo->exec("INSERT INTO 24_tags (name, slug) VALUES 
    ('PHP', 'php'),
    ('JavaScript', 'javascript'),
    ('Tutorial', 'tutorial'),
    ('News', 'news'),
    ('Tips', 'tips')
");
echo "✅ Created 5 tags\n";

// Posts
$pdo->exec("
    INSERT INTO 24_posts (title, slug, content, excerpt, status, category_id, published_at) VALUES 
    (
        'Welcome to DynamicCRUD Universal CMS',
        'welcome-to-dynamiccrud',
        '<p>Welcome to your new blog powered by <strong>DynamicCRUD Universal CMS</strong>!</p><p>This is not just another CMS - it is a <em>Universal CMS</em> that starts as a blog and grows into anything you need.</p><h2>What makes it special?</h2><ul><li>10x faster than WordPress - Load times under 500ms</li><li>Secure by design - No plugins = no vulnerabilities</li><li>Mobile-first - Responsive out of the box</li><li>SEO built-in - Meta tags, Open Graph, Schema.org</li><li>Grows with you - Add e-commerce, CRM, or custom features</li></ul><p>Edit or delete this post to get started!</p>',
        'Welcome to your new blog powered by DynamicCRUD Universal CMS - the CMS that grows with you!',
        'published',
        1,
        NOW()
    ),
    (
        'Getting Started with DynamicCRUD',
        'getting-started',
        '<p>Getting started with DynamicCRUD is incredibly simple. Here is what you need to know:</p><h2>Installation</h2><p>Just 3 lines of code to get a working blog.</p><h2>Creating Content</h2><p>Use the admin panel to create posts, categories, and tags. Everything is intuitive and fast.</p><h2>Customization</h2><p>Customize your blog with themes, or build your own. The possibilities are endless!</p>',
        'Learn how to get started with DynamicCRUD in just a few minutes.',
        'published',
        1,
        NOW()
    ),
    (
        '10 Reasons to Switch from WordPress',
        '10-reasons-switch-wordpress',
        '<p>Thinking about switching from WordPress? Here are 10 compelling reasons:</p><ol><li>Speed - 6x faster load times</li><li>Security - No plugin vulnerabilities</li><li>Simplicity - Clean, intuitive interface</li><li>Cost - 10x cheaper than WordPress + plugins</li><li>Flexibility - Grows into any application</li><li>SEO - Built-in optimization</li><li>Performance - Optimized database queries</li><li>Maintenance - Zero plugin conflicts</li><li>Modern - PHP 8.0+ with latest features</li><li>Future-proof - AI-ready architecture</li></ol><p>Ready to make the switch? It takes just 10 minutes!</p>',
        'Discover why thousands are switching from WordPress to DynamicCRUD.',
        'published',
        3,
        NOW()
    )
");
echo "✅ Created 3 posts\n";

// Link posts to tags
$pdo->exec("
    INSERT INTO 24_post_tags (post_id, tag_id) VALUES 
    (1, 1), (1, 3),
    (2, 1), (2, 3),
    (3, 4), (3, 5)
");
echo "✅ Linked posts to tags\n\n";

// Generate sitemap
echo "🗺️  Generating sitemap...\n";
$seo = new SEOManager($pdo, 'http://localhost/examples/24-blog-cms', 'My Blog');
$sitemap = $seo->generateSitemap();
file_put_contents(__DIR__ . '/sitemap.xml', $sitemap);
echo "✅ Sitemap generated: sitemap.xml\n";

// Generate RSS
echo "📡 Generating RSS feed...\n";
$rss = $seo->generateRSS();
file_put_contents(__DIR__ . '/feed.xml', $rss);
echo "✅ RSS feed generated: feed.xml\n\n";

echo "🎉 Blog CMS setup complete!\n\n";
echo "📍 Next steps:\n";
echo "   1. Visit: http://localhost/examples/24-blog-cms/\n";
echo "   2. Admin: http://localhost/examples/24-blog-cms/admin.php\n";
echo "   3. Sitemap: http://localhost/examples/24-blog-cms/sitemap.xml\n";
echo "   4. RSS: http://localhost/examples/24-blog-cms/feed.xml\n";
