<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\Frontend\FrontendRenderer;
use Morpheus\ContentTypes\ContentTypeManager;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class FrontendRendererTest extends TestCase
{
    private \PDO $pdo;
    private FrontendRenderer $renderer;
    
    protected function setUp(): void
    {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        // Install blog content type
        $manager = new ContentTypeManager($this->pdo);
        $manager->install('blog');
        
        $this->renderer = new FrontendRenderer($this->pdo);
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
        // Create category
        $this->pdo->exec("INSERT INTO categories (id, name, slug) VALUES (1, 'Technology', 'technology')");
        
        // Create tag
        $this->pdo->exec("INSERT INTO tags (id, name, slug) VALUES (1, 'PHP', 'php')");
        
        // Create posts
        $this->pdo->exec("
            INSERT INTO posts (id, title, slug, content, excerpt, status, category_id, published_at) VALUES
            (1, 'First Post', 'first-post', 'Content of first post', 'Excerpt 1', 'published', 1, NOW()),
            (2, 'Second Post', 'second-post', 'Content of second post', 'Excerpt 2', 'published', 1, NOW()),
            (3, 'Draft Post', 'draft-post', 'Draft content', 'Draft excerpt', 'draft', 1, NOW())
        ");
        
        // Link post to tag
        $this->pdo->exec("INSERT INTO post_tags (post_id, tag_id) VALUES (1, 1)");
    }
    
    public function testRenderSingle(): void
    {
        $html = $this->renderer->renderSingle('first-post');
        
        $this->assertStringContainsString('First Post', $html);
        $this->assertStringContainsString('Content of first post', $html);
    }
    
    public function testRenderSingleNotFound(): void
    {
        $html = $this->renderer->renderSingle('non-existent');
        
        $this->assertStringContainsString('404', $html);
    }
    
    public function testRenderArchive(): void
    {
        $html = $this->renderer->renderArchive();
        
        $this->assertStringContainsString('First Post', $html);
        $this->assertStringContainsString('Second Post', $html);
        $this->assertStringNotContainsString('Draft Post', $html); // Drafts not shown
    }
    
    public function testRenderCategory(): void
    {
        $html = $this->renderer->renderCategory('technology');
        
        $this->assertStringContainsString('Technology', $html);
        $this->assertStringContainsString('First Post', $html);
    }
    
    public function testRenderCategoryNotFound(): void
    {
        $html = $this->renderer->renderCategory('non-existent');
        
        $this->assertStringContainsString('404', $html);
    }
    
    public function testRenderTag(): void
    {
        $html = $this->renderer->renderTag('php');
        
        $this->assertStringContainsString('PHP', $html);
        $this->assertStringContainsString('First Post', $html);
    }
    
    public function testRenderTagNotFound(): void
    {
        $html = $this->renderer->renderTag('non-existent');
        
        $this->assertStringContainsString('404', $html);
    }
    
    public function testRenderHome(): void
    {
        $html = $this->renderer->renderHome();
        
        $this->assertStringContainsString('First Post', $html);
        $this->assertStringContainsString('Second Post', $html);
    }
    
    public function testRenderSearch(): void
    {
        $html = $this->renderer->renderSearch('First');
        
        $this->assertStringContainsString('First Post', $html);
        $this->assertStringNotContainsString('Second Post', $html);
    }
    
    public function testRender404(): void
    {
        $html = $this->renderer->render404();
        
        $this->assertStringContainsString('404', $html);
        $this->assertStringContainsString('Not Found', $html);
    }
}
