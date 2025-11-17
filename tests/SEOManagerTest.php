<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\Frontend\SEOManager;
use Morpheus\ContentTypes\ContentTypeManager;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class SEOManagerTest extends TestCase
{
    private \PDO $pdo;
    private SEOManager $seo;
    
    protected function setUp(): void
    {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        // Install blog content type
        $manager = new ContentTypeManager($this->pdo);
        $manager->install('blog');
        
        $this->seo = new SEOManager($this->pdo, 'https://example.com', 'Test Blog');
        $this->seedTestData();
    }
    
    protected function tearDown(): void
    {
        $this->cleanup();
    }
    
    private function cleanup(): void
    {
        try {
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $this->pdo->exec("DROP TABLE IF EXISTS comments");
            $this->pdo->exec("DROP TABLE IF EXISTS post_tags");
            $this->pdo->exec("DROP TABLE IF EXISTS tags");
            $this->pdo->exec("DROP TABLE IF EXISTS posts");
            $this->pdo->exec("DROP TABLE IF EXISTS categories");
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        } catch (\Exception $e) {
            // Ignore
        }
    }
    
    private function seedTestData(): void
    {
        $this->pdo->exec("INSERT INTO categories (id, name, slug) VALUES (1, 'Tech', 'tech')");
        $this->pdo->exec("INSERT INTO tags (id, name, slug) VALUES (1, 'PHP', 'php')");
        $this->pdo->exec("
            INSERT INTO posts (id, title, slug, content, excerpt, status, featured_image, published_at) VALUES
            (1, 'Test Post', 'test-post', 'Content here', 'Excerpt here', 'published', 'image.jpg', NOW()),
            (2, 'Second Post', 'second-post', 'More content', 'More excerpt', 'published', NULL, NOW())
        ");
    }
    
    public function testGenerateMetaTags(): void
    {
        $post = [
            'title' => 'My Post',
            'slug' => 'my-post',
            'excerpt' => 'This is my post',
            'featured_image' => 'image.jpg'
        ];
        
        $html = $this->seo->generateMetaTags($post);
        
        $this->assertStringContainsString('<title>My Post - Test Blog</title>', $html);
        $this->assertStringContainsString('name="description"', $html);
        $this->assertStringContainsString('This is my post', $html);
        $this->assertStringContainsString('rel="canonical"', $html);
        $this->assertStringContainsString('https://example.com/blog/my-post', $html);
    }
    
    public function testGenerateOpenGraph(): void
    {
        $post = [
            'title' => 'My Post',
            'slug' => 'my-post',
            'excerpt' => 'This is my post',
            'featured_image' => 'image.jpg'
        ];
        
        $html = $this->seo->generateMetaTags($post);
        
        $this->assertStringContainsString('property="og:type"', $html);
        $this->assertStringContainsString('property="og:title"', $html);
        $this->assertStringContainsString('property="og:description"', $html);
        $this->assertStringContainsString('property="og:url"', $html);
        $this->assertStringContainsString('property="og:image"', $html);
    }
    
    public function testGenerateTwitterCard(): void
    {
        $post = [
            'title' => 'My Post',
            'slug' => 'my-post',
            'excerpt' => 'This is my post'
        ];
        
        $html = $this->seo->generateMetaTags($post);
        
        $this->assertStringContainsString('name="twitter:card"', $html);
        $this->assertStringContainsString('name="twitter:title"', $html);
        $this->assertStringContainsString('name="twitter:description"', $html);
    }
    
    public function testGenerateSchemaOrg(): void
    {
        $post = [
            'title' => 'My Post',
            'slug' => 'my-post',
            'excerpt' => 'This is my post',
            'published_at' => '2025-01-01 12:00:00',
            'updated_at' => '2025-01-02 12:00:00'
        ];
        
        $html = $this->seo->generateSchemaOrg($post);
        
        $this->assertStringContainsString('application/ld+json', $html);
        $this->assertStringContainsString('schema.org', $html);
        $this->assertStringContainsString('BlogPosting', $html);
        $this->assertStringContainsString('My Post', $html);
    }
    
    public function testGenerateSitemap(): void
    {
        $xml = $this->seo->generateSitemap();
        
        $this->assertStringContainsString('<?xml version="1.0"', $xml);
        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringContainsString('https://example.com', $xml);
        $this->assertStringContainsString('test-post', $xml);
        $this->assertStringContainsString('second-post', $xml);
        $this->assertStringContainsString('<loc>', $xml);
        $this->assertStringContainsString('<lastmod>', $xml);
        $this->assertStringContainsString('<changefreq>', $xml);
        $this->assertStringContainsString('<priority>', $xml);
    }
    
    public function testGenerateRSS(): void
    {
        $xml = $this->seo->generateRSS();
        
        $this->assertStringContainsString('<?xml version="1.0"', $xml);
        $this->assertStringContainsString('<rss version="2.0"', $xml);
        $this->assertStringContainsString('<channel>', $xml);
        $this->assertStringContainsString('<title>Test Blog</title>', $xml);
        $this->assertStringContainsString('Test Post', $xml);
        $this->assertStringContainsString('Second Post', $xml);
        $this->assertStringContainsString('<item>', $xml);
        $this->assertStringContainsString('<pubDate>', $xml);
    }
    
    public function testSitemapIncludesCategories(): void
    {
        $xml = $this->seo->generateSitemap();
        
        $this->assertStringContainsString('/blog/category/tech', $xml);
    }
    
    public function testSitemapIncludesTags(): void
    {
        $xml = $this->seo->generateSitemap();
        
        $this->assertStringContainsString('/blog/tag/php', $xml);
    }
    
    public function testRSSLimit(): void
    {
        // Add more posts
        for ($i = 3; $i <= 25; $i++) {
            $this->pdo->exec("INSERT INTO posts (title, slug, content, status, published_at) VALUES ('Post {$i}', 'post-{$i}', 'Content', 'published', NOW())");
        }
        
        $xml = $this->seo->generateRSS(10);
        
        // Should only have 10 items
        $this->assertEquals(10, substr_count($xml, '<item>'));
    }
}
