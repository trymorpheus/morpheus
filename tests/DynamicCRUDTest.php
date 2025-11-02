<?php

namespace DynamicCRUD\Tests;

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;
use PHPUnit\Framework\TestCase;
use PDO;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class DynamicCRUDTest extends TestCase
{
    private PDO $pdo;
    private DynamicCRUD $crud;

    protected function setUp(): void
    {
        $this->pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s', getenv('DB_HOST'), getenv('DB_NAME')),
            getenv('DB_USER'),
            getenv('DB_PASS')
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->crud = new DynamicCRUD($this->pdo, 'users');
        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
    }

    private function cleanupTestData(): void
    {
        $this->pdo->exec("DELETE FROM users WHERE email LIKE 'integration_%@test.com'");
    }

    public function testConstructor(): void
    {
        $crud = new DynamicCRUD($this->pdo, 'users');
        $this->assertInstanceOf(DynamicCRUD::class, $crud);
    }

    public function testConstructorWithCache(): void
    {
        $cache = new FileCacheStrategy(__DIR__ . '/temp_cache');
        $crud = new DynamicCRUD($this->pdo, 'users', $cache);
        
        $this->assertInstanceOf(DynamicCRUD::class, $crud);
        
        if (is_dir(__DIR__ . '/temp_cache')) {
            $files = glob(__DIR__ . '/temp_cache/*');
            foreach ($files as $file) {
                if (is_file($file)) unlink($file);
            }
            rmdir(__DIR__ . '/temp_cache');
        }
    }

    public function testRenderFormCreate(): void
    {
        $html = $this->crud->renderForm();
        
        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('csrf_token', $html);
    }

    public function testRenderFormEdit(): void
    {
        $id = $this->createTestUser();
        $html = $this->crud->renderForm($id);
        
        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('value="Test User"', $html);
        $this->assertStringContainsString('name="id"', $html);
    }

    public function testCompleteCreateFlow(): void
    {
        session_start();
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        
        $_POST = [
            'csrf_token' => $token,
            'name' => 'Integration Test',
            'email' => 'integration_create@test.com',
            'password' => 'test123'
        ];

        $result = $this->crud->handleSubmission();

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('id', $result);
        
        $user = $this->findByEmail('integration_create@test.com');
        $this->assertNotNull($user);
        $this->assertEquals('Integration Test', $user['name']);
    }

    public function testCompleteUpdateFlow(): void
    {
        $id = $this->createTestUser();
        
        session_start();
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        
        $_POST = [
            'csrf_token' => $token,
            'id' => $id,
            'name' => 'Updated Name',
            'email' => 'integration_test@test.com',
            'password' => 'test123'
        ];

        $result = $this->crud->handleSubmission();

        $this->assertTrue($result['success']);
        
        $user = $this->findByEmail('integration_test@test.com');
        $this->assertEquals('Updated Name', $user['name']);
    }

    public function testDeleteOperation(): void
    {
        $id = $this->createTestUser();
        
        $result = $this->crud->delete($id);
        
        $this->assertTrue($result);
        $this->assertNull($this->findByEmail('integration_test@test.com'));
    }

    public function testListOperation(): void
    {
        $this->createTestUser();
        $this->createTestUser('integration_test2@test.com');
        
        $result = $this->crud->list(['perPage' => 10]);
        
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('pagination', $result);
        $this->assertGreaterThanOrEqual(2, count($result['data']));
    }

    public function testHooksIntegration(): void
    {
        $hookExecuted = false;
        
        $this->crud->beforeSave(function($data) use (&$hookExecuted) {
            $hookExecuted = true;
            $data['name'] = strtoupper($data['name']);
            return $data;
        });

        session_start();
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        
        $_POST = [
            'csrf_token' => $token,
            'name' => 'lowercase',
            'email' => 'integration_hooks@test.com',
            'password' => 'test123'
        ];

        $result = $this->crud->handleSubmission();

        $this->assertTrue($hookExecuted);
        $this->assertTrue($result['success']);
        
        $user = $this->findByEmail('integration_hooks@test.com');
        $this->assertEquals('LOWERCASE', $user['name']);
    }

    public function testFluentInterfaceChaining(): void
    {
        $result = $this->crud
            ->beforeSave(function($data) { return $data; })
            ->afterSave(function($id, $data) {})
            ->enableAudit(1);

        $this->assertInstanceOf(DynamicCRUD::class, $result);
    }

    public function testAuditIntegration(): void
    {
        $this->crud->enableAudit(42);

        session_start();
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        
        $_POST = [
            'csrf_token' => $token,
            'name' => 'Audit Test',
            'email' => 'integration_audit@test.com',
            'password' => 'test123'
        ];

        $result = $this->crud->handleSubmission();
        $this->assertTrue($result['success']);

        $history = $this->crud->getAuditHistory($result['id']);
        
        $this->assertNotEmpty($history);
        $this->assertEquals('CREATE', $history[0]['action']);
        $this->assertEquals(42, $history[0]['user_id']);
    }

    public function testManyToManyConfiguration(): void
    {
        $result = $this->crud->addManyToMany(
            'tags',
            'post_tags',
            'post_id',
            'tag_id',
            'tags'
        );

        $this->assertInstanceOf(DynamicCRUD::class, $result);
    }

    public function testValidationFailure(): void
    {
        session_start();
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        
        $_POST = [
            'csrf_token' => $token,
            'name' => 'Test'
            // Missing required email
        ];

        $result = $this->crud->handleSubmission();

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('email', $result['errors']);
    }

    public function testCsrfValidation(): void
    {
        $_POST = [
            'csrf_token' => 'invalid_token',
            'name' => 'Test',
            'email' => 'test@example.com'
        ];

        $result = $this->crud->handleSubmission();

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('CSRF', $result['error']);
    }

    private function createTestUser(string $email = 'integration_test@test.com'): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
        $stmt->execute(['name' => 'Test User', 'email' => $email, 'password' => 'test123']);
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
