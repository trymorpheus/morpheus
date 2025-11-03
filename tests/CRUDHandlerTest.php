<?php

namespace DynamicCRUD\Tests;

use DynamicCRUD\CRUDHandler;
use PHPUnit\Framework\TestCase;
use PDO;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class CRUDHandlerTest extends TestCase
{
    private PDO $pdo;
    private CRUDHandler $handler;

    protected function setUp(): void
    {
        $this->pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s', getenv('DB_HOST'), getenv('DB_NAME')),
            getenv('DB_USER'),
            getenv('DB_PASS')
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->handler = new CRUDHandler($this->pdo, 'users');
        
        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }

    private function cleanupTestData(): void
    {
        $this->pdo->exec("DELETE FROM users WHERE email LIKE 'test_%@example.com'");
    }

    public function testConstructor(): void
    {
        $handler = new CRUDHandler($this->pdo, 'users');
        $this->assertInstanceOf(CRUDHandler::class, $handler);
    }

    public function testBeforeValidateHook(): void
    {
        $executed = false;
        
        $this->handler->beforeValidate(function($data) use (&$executed) {
            $executed = true;
            $data['name'] = strtoupper($data['name']);
            return $data;
        });

        $_POST = [
            'csrf_token' => $this->generateValidToken(),
            'name' => 'john',
            'email' => 'test_hook1@example.com',
            'password' => 'test12345'
        ];

        $result = $this->handler->handleSubmission();
        
        $this->assertTrue($executed);
        $this->assertTrue($result['success']);
        
        $record = $this->findByEmail('test_hook1@example.com');
        $this->assertEquals('JOHN', $record['name']);
    }

    public function testAfterValidateHook(): void
    {
        $executed = false;
        
        $this->handler->afterValidate(function($data) use (&$executed) {
            $executed = true;
            return $data;
        });

        $_POST = [
            'csrf_token' => $this->generateValidToken(),
            'name' => 'Test',
            'email' => 'test_hook2@example.com',
            'password' => 'test12345'
        ];

        $this->handler->handleSubmission();
        $this->assertTrue($executed);
    }

    public function testBeforeSaveHook(): void
    {
        $executed = false;
        
        $this->handler->beforeSave(function($data) use (&$executed) {
            $executed = true;
            return $data;
        });

        $_POST = [
            'csrf_token' => $this->generateValidToken(),
            'name' => 'Test',
            'email' => 'test_hook3@example.com',
            'password' => 'test12345'
        ];

        $this->handler->handleSubmission();
        $this->assertTrue($executed);
    }

    public function testAfterSaveHook(): void
    {
        $capturedId = null;
        
        $this->handler->afterSave(function($id, $data) use (&$capturedId) {
            $capturedId = $id;
        });

        $_POST = [
            'csrf_token' => $this->generateValidToken(),
            'name' => 'Test',
            'email' => 'test_hook4@example.com',
            'password' => 'test12345'
        ];

        $result = $this->handler->handleSubmission();
        
        $this->assertNotNull($capturedId);
        $this->assertEquals($result['id'], $capturedId);
    }

    public function testBeforeCreateHook(): void
    {
        $executed = false;
        
        $this->handler->beforeCreate(function($data) use (&$executed) {
            $executed = true;
            return $data;
        });

        $_POST = [
            'csrf_token' => $this->generateValidToken(),
            'name' => 'Test',
            'email' => 'test_hook5@example.com',
            'password' => 'test12345'
        ];

        $this->handler->handleSubmission();
        $this->assertTrue($executed);
    }

    public function testAfterCreateHook(): void
    {
        $capturedId = null;
        
        $this->handler->afterCreate(function($id, $data) use (&$capturedId) {
            $capturedId = $id;
        });

        $_POST = [
            'csrf_token' => $this->generateValidToken(),
            'name' => 'Test',
            'email' => 'test_hook6@example.com',
            'password' => 'test12345'
        ];

        $result = $this->handler->handleSubmission();
        
        $this->assertNotNull($capturedId);
        $this->assertEquals($result['id'], $capturedId);
    }

    public function testBeforeUpdateHook(): void
    {
        $id = $this->createTestUser('test_update1@example.com');
        $executed = false;
        
        $this->handler->beforeUpdate(function($data, $id) use (&$executed) {
            $executed = true;
            return $data;
        });

        $_POST = [
            'csrf_token' => $this->generateValidToken(),
            'id' => $id,
            'name' => 'Updated',
            'email' => 'test_update1@example.com',
            'password' => 'test12345'
        ];

        $this->handler->handleSubmission();
        $this->assertTrue($executed);
    }

    public function testAfterUpdateHook(): void
    {
        $id = $this->createTestUser('test_update2@example.com');
        $capturedId = null;
        
        $this->handler->afterUpdate(function($id, $data) use (&$capturedId) {
            $capturedId = $id;
        });

        $_POST = [
            'csrf_token' => $this->generateValidToken(),
            'id' => $id,
            'name' => 'Updated',
            'email' => 'test_update2@example.com',
            'password' => 'test12345'
        ];

        $this->handler->handleSubmission();
        $this->assertEquals($id, $capturedId);
    }

    public function testBeforeDeleteHook(): void
    {
        $id = $this->createTestUser('test_delete1@example.com');
        $executed = false;
        
        $this->handler->beforeDelete(function($deleteId) use (&$executed, $id) {
            $executed = true;
            $this->assertEquals($id, $deleteId);
        });

        $this->handler->delete($id);
        $this->assertTrue($executed);
    }

    public function testAfterDeleteHook(): void
    {
        $id = $this->createTestUser('test_delete2@example.com');
        $executed = false;
        
        $this->handler->afterDelete(function($deleteId) use (&$executed, $id) {
            $executed = true;
            $this->assertEquals($id, $deleteId);
        });

        $this->handler->delete($id);
        $this->assertTrue($executed);
    }

    public function testCreateRecord(): void
    {
        $_POST = [
            'csrf_token' => $this->generateValidToken(),
            'name' => 'John Doe',
            'email' => 'test_create@example.com',
            'password' => 'test12345'
        ];

        $result = $this->handler->handleSubmission();

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('id', $result);
        $this->assertGreaterThan(0, $result['id']);
    }

    public function testUpdateRecord(): void
    {
        $id = $this->createTestUser('test_update@example.com');

        $_POST = [
            'csrf_token' => $this->generateValidToken(),
            'id' => $id,
            'name' => 'Jane Doe',
            'email' => 'test_update@example.com',
            'password' => 'test12345'
        ];

        $result = $this->handler->handleSubmission();

        $this->assertTrue($result['success']);
        $this->assertEquals($id, $result['id']);
        
        $record = $this->findByEmail('test_update@example.com');
        $this->assertEquals('Jane Doe', $record['name']);
    }

    public function testDeleteRecord(): void
    {
        $id = $this->createTestUser('test_delete@example.com');
        
        $result = $this->handler->delete($id);
        
        $this->assertTrue($result);
        $this->assertNull($this->findByEmail('test_delete@example.com'));
    }

    public function testTransactionRollbackOnError(): void
    {
        $this->handler->afterSave(function() {
            throw new \Exception('Simulated error');
        });

        $_POST = [
            'csrf_token' => $this->generateValidToken(),
            'name' => 'Test',
            'email' => 'test_rollback@example.com',
            'password' => 'test12345'
        ];

        $result = $this->handler->handleSubmission();

        $this->assertFalse($result['success']);
        $this->assertNull($this->findByEmail('test_rollback@example.com'));
    }

    public function testInvalidCsrfToken(): void
    {
        $_POST = [
            'csrf_token' => 'invalid_token',
            'name' => 'Test',
            'email' => 'test_csrf@example.com'
        ];

        $result = $this->handler->handleSubmission();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('CSRF', $result['error']);
    }

    public function testValidationFailure(): void
    {
        $_POST = [
            'csrf_token' => $this->generateValidToken(),
            'name' => 'Test'
            // Missing required email
        ];

        $result = $this->handler->handleSubmission();

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
    }

    public function testFluentInterface(): void
    {
        $result = $this->handler
            ->beforeSave(function($data) { return $data; })
            ->afterSave(function($id, $data) {})
            ->beforeCreate(function($data) { return $data; });

        $this->assertInstanceOf(CRUDHandler::class, $result);
    }

    public function testAddManyToMany(): void
    {
        $result = $this->handler->addManyToMany(
            'tags',
            'post_tags',
            'post_id',
            'tag_id',
            'tags'
        );

        $this->assertInstanceOf(CRUDHandler::class, $result);
        
        $relations = $this->handler->getManyToManyRelations();
        $this->assertArrayHasKey('tags', $relations);
        $this->assertEquals('post_tags', $relations['tags']['pivot_table']);
    }

    public function testEnableAudit(): void
    {
        $result = $this->handler->enableAudit(1);
        $this->assertInstanceOf(CRUDHandler::class, $result);
    }

    private function generateValidToken(): string
    {
        session_start();
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    private function createTestUser(string $email): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
        $stmt->execute(['name' => 'Test User', 'email' => $email, 'password' => 'test12345']);
        return (int) $this->pdo->lastInsertId();
    }

    private function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
