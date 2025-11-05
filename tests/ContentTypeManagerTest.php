<?php

namespace DynamicCRUD\Tests;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\ContentTypes\ContentTypeManager;
use DynamicCRUD\ContentTypes\BlogContentType;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ContentTypeManagerTest extends TestCase
{
    private \PDO $pdo;
    private ContentTypeManager $manager;
    
    protected function setUp(): void
    {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->manager = new ContentTypeManager($this->pdo);
        $this->cleanup();
    }
    
    protected function tearDown(): void
    {
        $this->cleanup();
    }
    
    private function cleanup(): void
    {
        try {
            $this->pdo->exec("DROP TABLE IF EXISTS comments");
            $this->pdo->exec("DROP TABLE IF EXISTS post_tags");
            $this->pdo->exec("DROP TABLE IF EXISTS tags");
            $this->pdo->exec("DROP TABLE IF EXISTS posts");
            $this->pdo->exec("DROP TABLE IF EXISTS categories");
        } catch (\Exception $e) {
            // Ignore errors
        }
    }
    
    public function testGetAvailableContentTypes(): void
    {
        $available = $this->manager->getAvailable();
        
        $this->assertIsArray($available);
        $this->assertNotEmpty($available);
        $this->assertArrayHasKey('blog', $available);
    }
    
    public function testInstallBlogContentType(): void
    {
        $result = $this->manager->install('blog');
        
        $this->assertTrue($result);
        $this->assertTrue($this->manager->isInstalled('blog'));
        
        // Verify tables exist
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'posts'");
        $this->assertEquals(1, $stmt->rowCount());
        
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'categories'");
        $this->assertEquals(1, $stmt->rowCount());
        
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'tags'");
        $this->assertEquals(1, $stmt->rowCount());
    }
    
    public function testUninstallBlogContentType(): void
    {
        $this->manager->install('blog');
        $result = $this->manager->uninstall('blog');
        
        $this->assertTrue($result);
        $this->assertFalse($this->manager->isInstalled('blog'));
        
        // Verify tables don't exist
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'posts'");
        $this->assertEquals(0, $stmt->rowCount());
    }
    
    public function testGetInstalledContentTypes(): void
    {
        $this->manager->install('blog');
        $installed = $this->manager->getInstalled();
        
        $this->assertIsArray($installed);
        $this->assertArrayHasKey('blog', $installed);
        $this->assertTrue($installed['blog']['installed']);
    }
    
    public function testInstallNonExistentContentType(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Content type 'nonexistent' not found");
        
        $this->manager->install('nonexistent');
    }
}
