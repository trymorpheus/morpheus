<?php

namespace DynamicCRUD\Tests;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\DynamicCRUD;
use PDO;

class SoftDeletesTest extends TestCase
{
    private PDO $pdo;
    private DynamicCRUD $crud;

    protected function setUp(): void
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->pdo->exec("DROP TABLE IF EXISTS test_soft_posts");
        $this->pdo->exec("
            CREATE TABLE test_soft_posts (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                content TEXT,
                deleted_at TIMESTAMP NULL DEFAULT NULL
            ) COMMENT = '{
                \"behaviors\": {
                    \"soft_deletes\": {
                        \"enabled\": true,
                        \"column\": \"deleted_at\"
                    }
                }
            }'
        ");
        
        $this->crud = new DynamicCRUD($this->pdo, 'test_soft_posts');
        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
        $this->pdo->exec("DROP TABLE IF EXISTS test_soft_posts");
    }

    private function cleanupTestData(): void
    {
        $this->pdo->exec("DELETE FROM test_soft_posts WHERE title LIKE 'Test%'");
    }

    private function createTestPost(string $title = 'Test Post'): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO test_soft_posts (title, content) VALUES (:title, :content)");
        $stmt->execute(['title' => $title, 'content' => 'Test content']);
        return (int) $this->pdo->lastInsertId();
    }

    public function testSoftDeleteMarksRecordAsDeleted(): void
    {
        $id = $this->createTestPost();
        
        $this->crud->delete($id);
        
        $stmt = $this->pdo->prepare("SELECT deleted_at FROM test_soft_posts WHERE id = ?");
        $stmt->execute([$id]);
        $deletedAt = $stmt->fetchColumn();
        
        $this->assertNotNull($deletedAt);
    }

    public function testSoftDeleteDoesNotRemoveRecord(): void
    {
        $id = $this->createTestPost();
        
        $this->crud->delete($id);
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM test_soft_posts WHERE id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        $this->assertEquals(1, $count);
    }

    public function testRestoreRecoveryDeletedRecord(): void
    {
        $id = $this->createTestPost();
        $this->crud->delete($id);
        
        $this->crud->restore($id);
        
        $stmt = $this->pdo->prepare("SELECT deleted_at FROM test_soft_posts WHERE id = ?");
        $stmt->execute([$id]);
        $deletedAt = $stmt->fetchColumn();
        
        $this->assertNull($deletedAt);
    }

    public function testForceDeleteRemovesRecordPermanently(): void
    {
        $id = $this->createTestPost();
        
        $this->crud->forceDelete($id);
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM test_soft_posts WHERE id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        $this->assertEquals(0, $count);
    }

    public function testForceDeleteWorksOnSoftDeletedRecords(): void
    {
        $id = $this->createTestPost();
        $this->crud->delete($id);
        
        $this->crud->forceDelete($id);
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM test_soft_posts WHERE id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        $this->assertEquals(0, $count);
    }

    public function testRestoreThrowsExceptionWhenSoftDeletesNotEnabled(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS test_no_soft");
        $this->pdo->exec("CREATE TABLE test_no_soft (id INT PRIMARY KEY AUTO_INCREMENT, title VARCHAR(255))");
        
        $stmt = $this->pdo->prepare("INSERT INTO test_no_soft (title) VALUES ('Test')");
        $stmt->execute();
        $id = (int) $this->pdo->lastInsertId();
        
        $crud = new DynamicCRUD($this->pdo, 'test_no_soft');
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Soft deletes not enabled');
        
        $crud->restore($id);
        
        $this->pdo->exec("DROP TABLE IF EXISTS test_no_soft");
    }

    public function testTableMetadataDetectsSoftDeletes(): void
    {
        $metadata = $this->crud->getTableMetadata();
        
        $this->assertTrue($metadata->hasSoftDeletes());
        $this->assertEquals('deleted_at', $metadata->getSoftDeleteColumn());
    }

    public function testSoftDeleteConfigReturnsCorrectData(): void
    {
        $metadata = $this->crud->getTableMetadata();
        $config = $metadata->getSoftDeleteConfig();
        
        $this->assertIsArray($config);
        $this->assertTrue($config['enabled']);
        $this->assertEquals('deleted_at', $config['column']);
    }

    public function testMultipleSoftDeletesAndRestores(): void
    {
        $id1 = $this->createTestPost('Test Post 1');
        $id2 = $this->createTestPost('Test Post 2');
        $id3 = $this->createTestPost('Test Post 3');
        
        $this->crud->delete($id1);
        $this->crud->delete($id2);
        
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM test_soft_posts WHERE deleted_at IS NOT NULL");
        $deletedCount = $stmt->fetchColumn();
        $this->assertEquals(2, $deletedCount);
        
        $this->crud->restore($id1);
        
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM test_soft_posts WHERE deleted_at IS NOT NULL");
        $deletedCount = $stmt->fetchColumn();
        $this->assertEquals(1, $deletedCount);
    }

    public function testSoftDeleteWithCustomColumn(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS test_custom_soft");
        $this->pdo->exec("
            CREATE TABLE test_custom_soft (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(255),
                removed_at TIMESTAMP NULL DEFAULT NULL
            ) COMMENT = '{
                \"behaviors\": {
                    \"soft_deletes\": {
                        \"enabled\": true,
                        \"column\": \"removed_at\"
                    }
                }
            }'
        ");
        
        $crud = new DynamicCRUD($this->pdo, 'test_custom_soft');
        $metadata = $crud->getTableMetadata();
        
        $this->assertTrue($metadata->hasSoftDeletes());
        $this->assertEquals('removed_at', $metadata->getSoftDeleteColumn());
        
        $this->pdo->exec("DROP TABLE IF EXISTS test_custom_soft");
    }
}
