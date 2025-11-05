<?php

namespace DynamicCRUD\Tests;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\ContentTypes\BlogContentType;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class BlogContentTypeTest extends TestCase
{
    private \PDO $pdo;
    private BlogContentType $blog;
    
    protected function setUp(): void
    {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->blog = new BlogContentType();
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
    
    public function testGetName(): void
    {
        $this->assertEquals('blog', $this->blog->getName());
    }
    
    public function testGetDescription(): void
    {
        $description = $this->blog->getDescription();
        $this->assertIsString($description);
        $this->assertNotEmpty($description);
    }
    
    public function testInstall(): void
    {
        $result = $this->blog->install($this->pdo);
        
        $this->assertTrue($result);
        
        // Verify all tables exist
        $tables = ['categories', 'tags', 'posts', 'post_tags', 'comments'];
        foreach ($tables as $table) {
            $stmt = $this->pdo->query("SHOW TABLES LIKE '{$table}'");
            $this->assertEquals(1, $stmt->rowCount(), "Table {$table} should exist");
        }
    }
    
    public function testInstallWithPrefix(): void
    {
        $blog = new BlogContentType('test_');
        $result = $blog->install($this->pdo);
        
        $this->assertTrue($result);
        
        // Verify prefixed tables exist
        $tables = ['test_categories', 'test_tags', 'test_posts', 'test_post_tags', 'test_comments'];
        foreach ($tables as $table) {
            $stmt = $this->pdo->query("SHOW TABLES LIKE '{$table}'");
            $this->assertEquals(1, $stmt->rowCount(), "Table {$table} should exist");
        }
        
        // Cleanup prefixed tables
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        foreach ($tables as $table) {
            $this->pdo->exec("DROP TABLE IF EXISTS {$table}");
        }
        $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    }
    
    public function testUninstall(): void
    {
        $this->blog->install($this->pdo);
        $result = $this->blog->uninstall($this->pdo);
        
        $this->assertTrue($result);
        
        // Verify all tables are gone
        $tables = ['categories', 'tags', 'posts', 'post_tags', 'comments'];
        foreach ($tables as $table) {
            $stmt = $this->pdo->query("SHOW TABLES LIKE '{$table}'");
            $this->assertEquals(0, $stmt->rowCount(), "Table {$table} should not exist");
        }
    }
    
    public function testIsInstalled(): void
    {
        $this->assertFalse($this->blog->isInstalled($this->pdo));
        
        $this->blog->install($this->pdo);
        $this->assertTrue($this->blog->isInstalled($this->pdo));
        
        $this->blog->uninstall($this->pdo);
        $this->assertFalse($this->blog->isInstalled($this->pdo));
    }
    
    public function testTableStructure(): void
    {
        $this->blog->install($this->pdo);
        
        // Check posts table structure
        $stmt = $this->pdo->query("DESCRIBE posts");
        $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        $expectedColumns = ['id', 'title', 'slug', 'content', 'excerpt', 'status', 'featured_image', 'published_at', 'created_at', 'updated_at'];
        foreach ($expectedColumns as $col) {
            $this->assertContains($col, $columns, "Column {$col} should exist in posts table");
        }
    }
    
    public function testForeignKeys(): void
    {
        $this->blog->install($this->pdo);
        
        // Check posts has category_id FK
        $stmt = $this->pdo->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = 'test' 
            AND TABLE_NAME = 'posts' 
            AND REFERENCED_TABLE_NAME = 'categories'
        ");
        $this->assertGreaterThan(0, $stmt->rowCount(), "posts should have FK to categories");
        
        // Check post_tags has FKs
        $stmt = $this->pdo->query("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = 'test' 
            AND TABLE_NAME = 'post_tags' 
            AND REFERENCED_TABLE_NAME IN ('posts', 'tags')
        ");
        $this->assertEquals(2, $stmt->rowCount(), "post_tags should have 2 FKs");
    }
    
    public function testTableMetadata(): void
    {
        $this->blog->install($this->pdo);
        
        // Check posts table has metadata in comment
        $stmt = $this->pdo->query("
            SELECT TABLE_COMMENT 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = 'test' 
            AND TABLE_NAME = 'posts'
        ");
        $comment = $stmt->fetchColumn();
        
        $this->assertNotEmpty($comment);
        $metadata = json_decode($comment, true);
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('display_name', $metadata);
    }
    
    public function testColumnMetadata(): void
    {
        $this->blog->install($this->pdo);
        
        // Check slug column has metadata
        $stmt = $this->pdo->query("
            SELECT COLUMN_COMMENT 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = 'test' 
            AND TABLE_NAME = 'posts' 
            AND COLUMN_NAME = 'slug'
        ");
        $comment = $stmt->fetchColumn();
        
        $this->assertNotEmpty($comment);
        $metadata = json_decode($comment, true);
        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('hidden', $metadata);
        $this->assertTrue($metadata['hidden']);
    }
}
