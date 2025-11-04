<?php

namespace DynamicCRUD\Tests;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\Security\AuthenticationManager;
use PDO;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class AuthenticationManagerTest extends TestCase
{
    private PDO $pdo;
    private AuthenticationManager $auth;
    private array $authConfig;

    protected function setUp(): void
    {
        $this->pdo = TestHelper::getPDO();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->pdo->exec("DROP TABLE IF EXISTS test_auth_users");
        $this->pdo->exec("
            CREATE TABLE test_auth_users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $this->authConfig = [
            'identifier_field' => 'email',
            'password_field' => 'password',
            'registration' => [
                'enabled' => true,
                'auto_login' => true,
                'default_role' => 'user'
            ],
            'login' => [
                'enabled' => true,
                'remember_me' => true,
                'max_attempts' => 5,
                'lockout_duration' => 900,
                'session_lifetime' => 7200
            ]
        ];
        
        $this->auth = new AuthenticationManager($this->pdo, 'test_auth_users', $this->authConfig);
        
        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
        $this->pdo->exec("DROP TABLE IF EXISTS test_auth_users");
    }

    private function cleanupTestData(): void
    {
        $this->pdo->exec("DELETE FROM test_auth_users WHERE email LIKE 'test_%@example.com'");
    }

    public function testRegisterCreatesNewUser(): void
    {
        $result = $this->auth->register([
            'name' => 'Test User',
            'email' => 'test_register@example.com',
            'password' => 'password123'
        ]);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('id', $result);
        $this->assertGreaterThan(0, $result['id']);
    }

    public function testRegisterHashesPassword(): void
    {
        $this->auth->register([
            'name' => 'Test User',
            'email' => 'test_hash@example.com',
            'password' => 'password123'
        ]);
        
        $stmt = $this->pdo->prepare("SELECT password FROM test_auth_users WHERE email = ?");
        $stmt->execute(['test_hash@example.com']);
        $hash = $stmt->fetchColumn();
        
        $this->assertNotEquals('password123', $hash);
        $this->assertTrue(password_verify('password123', $hash));
    }

    public function testRegisterSetsDefaultRole(): void
    {
        $this->auth->register([
            'name' => 'Test User',
            'email' => 'test_role@example.com',
            'password' => 'password123'
        ]);
        
        $stmt = $this->pdo->prepare("SELECT role FROM test_auth_users WHERE email = ?");
        $stmt->execute(['test_role@example.com']);
        $role = $stmt->fetchColumn();
        
        $this->assertEquals('user', $role);
    }

    public function testRegisterRejectsDuplicateEmail(): void
    {
        $this->auth->register([
            'name' => 'Test User',
            'email' => 'test_duplicate@example.com',
            'password' => 'password123'
        ]);
        
        $result = $this->auth->register([
            'name' => 'Another User',
            'email' => 'test_duplicate@example.com',
            'password' => 'password456'
        ]);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('already exists', $result['error']);
    }

    public function testRegisterRequiresEmailAndPassword(): void
    {
        $result = $this->auth->register([
            'name' => 'Test User'
        ]);
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('required', $result['error']);
    }

    public function testLoginWithValidCredentials(): void
    {
        $this->auth->register([
            'name' => 'Test User',
            'email' => 'test_login@example.com',
            'password' => 'password123'
        ]);
        
        $result = $this->auth->login('test_login@example.com', 'password123');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals('test_login@example.com', $result['user']['email']);
    }

    public function testLoginWithInvalidPassword(): void
    {
        $this->auth->register([
            'name' => 'Test User',
            'email' => 'test_invalid@example.com',
            'password' => 'password123'
        ]);
        
        $result = $this->auth->login('test_invalid@example.com', 'wrongpassword');
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid credentials', $result['error']);
    }

    public function testLoginWithNonexistentUser(): void
    {
        $result = $this->auth->login('nonexistent@example.com', 'password123');
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid credentials', $result['error']);
    }

    public function testLoginCreatesSession(): void
    {
        $this->auth->register([
            'name' => 'Test User',
            'email' => 'test_session@example.com',
            'password' => 'password123'
        ]);
        
        $this->auth->login('test_session@example.com', 'password123');
        
        $this->assertTrue($this->auth->isAuthenticated());
        $this->assertNotNull($this->auth->getCurrentUser());
    }

    public function testLogoutClearsSession(): void
    {
        $this->auth->register([
            'name' => 'Test User',
            'email' => 'test_logout@example.com',
            'password' => 'password123'
        ]);
        
        $this->auth->login('test_logout@example.com', 'password123');
        $this->assertTrue($this->auth->isAuthenticated());
        
        $this->auth->logout();
        $this->assertFalse($this->auth->isAuthenticated());
    }

    public function testGetCurrentUserReturnsUserData(): void
    {
        $this->auth->register([
            'name' => 'Test User',
            'email' => 'test_current@example.com',
            'password' => 'password123'
        ]);
        
        $this->auth->login('test_current@example.com', 'password123');
        $user = $this->auth->getCurrentUser();
        
        $this->assertIsArray($user);
        $this->assertEquals('Test User', $user['name']);
        $this->assertEquals('test_current@example.com', $user['email']);
    }

    public function testGetCurrentUserReturnsNullWhenNotAuthenticated(): void
    {
        $user = $this->auth->getCurrentUser();
        $this->assertNull($user);
    }

    public function testRateLimitingAfterMaxAttempts(): void
    {
        $this->auth->register([
            'name' => 'Test User',
            'email' => 'test_ratelimit@example.com',
            'password' => 'password123'
        ]);
        
        for ($i = 0; $i < 5; $i++) {
            $this->auth->login('test_ratelimit@example.com', 'wrongpassword');
        }
        
        $result = $this->auth->login('test_ratelimit@example.com', 'password123');
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Too many failed attempts', $result['error']);
    }

    public function testAutoLoginAfterRegistration(): void
    {
        $result = $this->auth->register([
            'name' => 'Test User',
            'email' => 'test_autologin@example.com',
            'password' => 'password123'
        ]);
        
        $this->assertTrue($result['success']);
        $this->assertTrue($this->auth->isAuthenticated());
    }

    public function testFiltersCsrfTokenFromRegistration(): void
    {
        $result = $this->auth->register([
            'name' => 'Test User',
            'email' => 'test_csrf@example.com',
            'password' => 'password123',
            'csrf_token' => 'fake_token_12345'
        ]);
        
        $this->assertTrue($result['success']);
        
        $stmt = $this->pdo->prepare("SELECT * FROM test_auth_users WHERE email = ?");
        $stmt->execute(['test_csrf@example.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertArrayNotHasKey('csrf_token', $user);
    }

    public function testFiltersActionFromRegistration(): void
    {
        $result = $this->auth->register([
            'name' => 'Test User',
            'email' => 'test_action@example.com',
            'password' => 'password123',
            'action' => 'register'
        ]);
        
        $this->assertTrue($result['success']);
        
        $stmt = $this->pdo->prepare("SELECT * FROM test_auth_users WHERE email = ?");
        $stmt->execute(['test_action@example.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertArrayNotHasKey('action', $user);
    }
}
