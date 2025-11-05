<?php

namespace DynamicCRUD\Tests;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\ContentTypes\ContentTypeManager;
use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Frontend\FrontendRenderer;
use DynamicCRUD\Frontend\SEOManager;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class BlogWorkflowTest extends TestCase
{
    private \PDO $pdo;
    
    protected function setUp(): void
    {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->cleanup();
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
    
    public function testCompleteWorkflow(): void
    {
        // Start session for CSRF
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        
        $security = new \DynamicCRUD\SecurityModule();
        
        // 1. Install blog content type
        $manager = new ContentTypeManager($this->pdo);
        $result = $manager->install('blog');
        $this->assertTrue($result);
        $this->assertTrue($manager->isInstalled('blog'));
        
        // 2. Create category using CRUD
        $crud = new DynamicCRUD($this->pdo, 'categories');
        $_POST = [
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Tech articles',
            'csrf_token' => $security->generateCsrfToken()
        ];
        $result = $crud->handleSubmission();
        $this->assertTrue($result['success']);
        $categoryId = $result['id'];
        
        // 3. Create tag using CRUD
        $crud = new DynamicCRUD($this->pdo, 'tags');
        $_POST = [
            'name' => 'PHP',
            'slug' => 'php',
            'csrf_token' => $security->generateCsrfToken()
        ];
        $result = $crud->handleSubmission();
        $this->assertTrue($result['success']);
        $tagId = $result['id'];
        
        // 4. Create post using CRUD
        $crud = new DynamicCRUD($this->pdo, 'posts');
        $_POST = [
            'title' => 'My First Post',
            'slug' => 'my-first-post',
            'content' => '<p>This is my first blog post!</p>',
            'excerpt' => 'First post excerpt',
            'status' => 'published',
            'category_id' => $categoryId,
            'published_at' => date('Y-m-d H:i:s'),
            'csrf_token' => $security->generateCsrfToken()
        ];
        $result = $crud->handleSubmission();
        $this->assertTrue($result['success']);
        $postId = $result['id'];
        
        // 5. Link post to tag (many-to-many)
        $this->pdo->exec("INSERT INTO post_tags (post_id, tag_id) VALUES ({$postId}, {$tagId})");
        
        // 6. Render frontend
        $renderer = new FrontendRenderer($this->pdo);
        
        // Home page
        $html = $renderer->renderHome();
        $this->assertStringContainsString('My First Post', $html);
        $this->assertStringContainsString('First post excerpt', $html);
        
        // Single post
        $html = $renderer->renderSingle('my-first-post');
        $this->assertStringContainsString('My First Post', $html);
        $this->assertStringContainsString('This is my first blog post!', $html);
        
        // Category archive
        $html = $renderer->renderCategory('technology');
        $this->assertStringContainsString('Technology', $html);
        $this->assertStringContainsString('My First Post', $html);
        
        // Tag archive
        $html = $renderer->renderTag('php');
        $this->assertStringContainsString('PHP', $html);
        $this->assertStringContainsString('My First Post', $html);
        
        // 7. Generate SEO
        $seo = new SEOManager($this->pdo, 'https://example.com', 'Test Blog');
        
        // Sitemap
        $xml = $seo->generateSitemap();
        $this->assertStringContainsString('my-first-post', $xml);
        $this->assertStringContainsString('technology', $xml);
        $this->assertStringContainsString('php', $xml);
        
        // RSS
        $xml = $seo->generateRSS();
        $this->assertStringContainsString('My First Post', $xml);
        $this->assertStringContainsString('First post excerpt', $xml);
        
        // 8. Update post
        $_POST = [
            'id' => $postId,
            'title' => 'My Updated Post',
            'slug' => 'my-first-post',
            'content' => '<p>Updated content!</p>',
            'excerpt' => 'Updated excerpt',
            'status' => 'published',
            'category_id' => $categoryId,
            'published_at' => date('Y-m-d H:i:s'),
            'csrf_token' => $security->generateCsrfToken()
        ];
        $result = $crud->handleSubmission();
        $this->assertTrue($result['success']);
        
        // Verify update
        $html = $renderer->renderSingle('my-first-post');
        $this->assertStringContainsString('My Updated Post', $html);
        $this->assertStringContainsString('Updated content!', $html);
        
        // 9. Uninstall
        $result = $manager->uninstall('blog');
        $this->assertTrue($result);
        $this->assertFalse($manager->isInstalled('blog'));
    }
    
    public function testMultiplePostsWithPagination(): void
    {
        // Install and create category
        $manager = new ContentTypeManager($this->pdo);
        $manager->install('blog');
        
        $this->pdo->exec("INSERT INTO categories (id, name, slug) VALUES (1, 'Tech', 'tech')");
        
        // Create 15 posts with different timestamps
        for ($i = 1; $i <= 15; $i++) {
            $timestamp = date('Y-m-d H:i:s', strtotime("-{$i} hours"));
            $this->pdo->exec("
                INSERT INTO posts (title, slug, content, excerpt, status, category_id, published_at) 
                VALUES ('Post {$i}', 'post-{$i}', 'Content {$i}', 'Excerpt {$i}', 'published', 1, '{$timestamp}')
            ");
        }
        
        // Render archive (default 10 per page)
        $renderer = new FrontendRenderer($this->pdo);
        $html = $renderer->renderArchive(1);
        
        // Should show 10 posts on page 1
        $this->assertStringContainsString('Post 1', $html);
        $this->assertStringContainsString('<article>', $html);
        
        // Page 2 should show remaining 5 posts
        $html = $renderer->renderArchive(2);
        $this->assertStringContainsString('<article>', $html);
        // Verify it's not empty
        $this->assertGreaterThan(1000, strlen($html));
    }
    
    public function testDraftPostsNotVisible(): void
    {
        $manager = new ContentTypeManager($this->pdo);
        $manager->install('blog');
        
        $this->pdo->exec("INSERT INTO categories (id, name, slug) VALUES (1, 'Tech', 'tech')");
        
        // Create published and draft posts
        $this->pdo->exec("
            INSERT INTO posts (title, slug, content, status, category_id, published_at) VALUES
            ('Published Post', 'published-post', 'Content', 'published', 1, NOW()),
            ('Draft Post', 'draft-post', 'Content', 'draft', 1, NOW())
        ");
        
        $renderer = new FrontendRenderer($this->pdo);
        
        // Archive should only show published
        $html = $renderer->renderArchive();
        $this->assertStringContainsString('Published Post', $html);
        $this->assertStringNotContainsString('Draft Post', $html);
        
        // Draft post should return 404
        $html = $renderer->renderSingle('draft-post');
        $this->assertStringContainsString('404', $html);
    }
    
    public function testSearchFunctionality(): void
    {
        $manager = new ContentTypeManager($this->pdo);
        $manager->install('blog');
        
        $this->pdo->exec("INSERT INTO categories (id, name, slug) VALUES (1, 'Tech', 'tech')");
        
        $this->pdo->exec("
            INSERT INTO posts (title, slug, content, excerpt, status, category_id, published_at) VALUES
            ('PHP Tutorial', 'php-tutorial', 'Learn PHP programming', 'PHP guide', 'published', 1, NOW()),
            ('JavaScript Guide', 'js-guide', 'Learn JavaScript', 'JS guide', 'published', 1, NOW())
        ");
        
        $renderer = new FrontendRenderer($this->pdo);
        
        // Search for PHP
        $html = $renderer->renderSearch('PHP');
        $this->assertStringContainsString('PHP Tutorial', $html);
        $this->assertStringNotContainsString('JavaScript Guide', $html);
        
        // Search for JavaScript
        $html = $renderer->renderSearch('JavaScript');
        $this->assertStringContainsString('JavaScript Guide', $html);
        $this->assertStringNotContainsString('PHP Tutorial', $html);
    }
}
