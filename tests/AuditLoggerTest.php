<?php

namespace DynamicCRUD\Tests;

use DynamicCRUD\AuditLogger;
use PHPUnit\Framework\TestCase;
use PDO;

class AuditLoggerTest extends TestCase
{
    private PDO $pdo;
    private AuditLogger $logger;

    protected function setUp(): void
    {
        $this->pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s', getenv('DB_HOST'), getenv('DB_NAME')),
            getenv('DB_USER'),
            getenv('DB_PASS')
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->logger = new AuditLogger($this->pdo);
        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }

    private function cleanupTestData(): void
    {
        $this->pdo->exec("DELETE FROM audit_log WHERE table_name = 'test_table'");
    }

    public function testLogCreate(): void
    {
        $this->logger->logCreate('test_table', 1, ['name' => 'Test', 'email' => 'test@example.com']);
        
        $history = $this->logger->getHistory('test_table', 1);
        
        $this->assertCount(1, $history);
        $this->assertEquals('create', $history[0]['action']);
        $this->assertEquals('test_table', $history[0]['table_name']);
        $this->assertEquals(1, $history[0]['record_id']);
    }

    public function testLogUpdate(): void
    {
        $oldValues = ['name' => 'Old Name'];
        $newValues = ['name' => 'New Name'];
        
        $this->logger->logUpdate('test_table', 2, $oldValues, $newValues);
        
        $history = $this->logger->getHistory('test_table', 2);
        
        $this->assertCount(1, $history);
        $this->assertEquals('update', $history[0]['action']);
        $this->assertStringContainsString('Old Name', $history[0]['old_values']);
        $this->assertStringContainsString('New Name', $history[0]['new_values']);
    }

    public function testLogDelete(): void
    {
        $oldValues = ['name' => 'Deleted', 'email' => 'deleted@example.com'];
        
        $this->logger->logDelete('test_table', 3, $oldValues);
        
        $history = $this->logger->getHistory('test_table', 3);
        
        $this->assertCount(1, $history);
        $this->assertEquals('delete', $history[0]['action']);
        $this->assertStringContainsString('Deleted', $history[0]['old_values']);
        $this->assertNull($history[0]['new_values']);
    }

    public function testSetUserId(): void
    {
        $this->logger->setUserId(42);
        $this->logger->logCreate('test_table', 4, ['name' => 'Test']);
        
        $history = $this->logger->getHistory('test_table', 4);
        
        $this->assertEquals(42, $history[0]['user_id']);
    }

    public function testGetHistoryMultipleEntries(): void
    {
        $this->logger->logCreate('test_table', 5, ['name' => 'Initial']);
        $this->logger->logUpdate('test_table', 5, ['name' => 'Initial'], ['name' => 'Updated']);
        $this->logger->logDelete('test_table', 5, ['name' => 'Updated']);
        
        $history = $this->logger->getHistory('test_table', 5);
        
        $this->assertCount(3, $history);
        
        $actions = array_column($history, 'action');
        $this->assertContains('create', $actions);
        $this->assertContains('update', $actions);
        $this->assertContains('delete', $actions);
    }

    public function testGetHistoryEmptyForNonExistent(): void
    {
        $history = $this->logger->getHistory('test_table', 999);
        $this->assertEmpty($history);
    }
}
