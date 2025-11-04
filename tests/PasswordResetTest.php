<?php

namespace DynamicCRUD\Tests;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\DynamicCRUD;
use PDO;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class PasswordResetTest extends TestCase
{
    private PDO $pdo;
    private DynamicCRUD $crud;

    protected function setUp(): void
    {
        $this->pdo = TestHelper::getPDO();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->pdo->exec("DROP TABLE IF EXISTS test_reset_users");
        $this->pdo->exec("
            CREATE TABLE test_reset_users (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) COMMENT = '{
                \"authentication\": {
                    \"enabled\": true,
                    \"identifier_field\": \"email\",
                    \"password_field\": \"password\"
                }
            }'
        ");
        
        $this->crud = new DynamicCRUD($this->pdo, 'test_reset_users');
        $this->crud->enableAuthentication();
        
        $this->cleanupTestData();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
        $this->pdo->exec("DROP TABLE IF EXISTS test_reset_users");
        $this->pdo->exec("DROP TABLE IF EXISTS password_resets");
    }

    private function cleanupTestData(): void
    {
        $this->pdo->exec("DELETE FROM test_reset_users WHERE email LIKE 'test_%@example.com'");
        
        try {
            $this->pdo->exec("DELETE FROM password_resets WHERE email LIKE 'test_%@example.com'");
        } catch (\Exception $e) {
            // Table might not exist yet
        }
    }

    private function createTestUser(string $email): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO test_reset_users (name, email, password) VALUES (:name, :email, :password)");
        $stmt->execute([
            'name' => 'Test User',
            'email' => $email,
            'password' => password_hash('oldpassword', PASSWORD_DEFAULT)
        ]);
    }

    public function testRequestPasswordResetCreatesToken(): void
    {
        $this->createTestUser('test_reset@example.com');
        
        $result = $this->crud->requestPasswordReset('test_reset@example.com');
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('token', $result);
        $this->assertIsString($result['token']);
        $this->assertEquals(64, strlen($result['token'])); // 32 bytes = 64 hex chars
    }

    public function testRequestPasswordResetFailsForNonexistentUser(): void
    {
        $result = $this->crud->requestPasswordReset('nonexistent@example.com');
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not found', $result['error']);
    }

    public function testValidateResetTokenReturnsEmail(): void
    {
        $this->createTestUser('test_validate@example.com');
        
        $result = $this->crud->requestPasswordReset('test_validate@example.com');
        $token = $result['token'];
        
        $email = $this->crud->validateResetToken($token);
        
        $this->assertEquals('test_validate@example.com', $email);
    }

    public function testValidateResetTokenReturnsNullForInvalidToken(): void
    {
        $email = $this->crud->validateResetToken('invalid_token_12345');
        
        $this->assertNull($email);
    }

    public function testValidateResetTokenReturnsNullForExpiredToken(): void
    {
        $this->createTestUser('test_expired@example.com');
        
        $result = $this->crud->requestPasswordReset('test_expired@example.com');
        $token = $result['token'];
        
        // Manually expire the token
        $this->pdo->exec("UPDATE password_resets SET expires_at = DATE_SUB(NOW(), INTERVAL 1 HOUR) WHERE token = '$token'");
        
        $email = $this->crud->validateResetToken($token);
        
        $this->assertNull($email);
    }

    public function testResetPasswordChangesPassword(): void
    {
        $this->createTestUser('test_change@example.com');
        
        $result = $this->crud->requestPasswordReset('test_change@example.com');
        $token = $result['token'];
        
        $result = $this->crud->resetPassword($token, 'newpassword123');
        
        $this->assertTrue($result['success']);
        
        // Verify password was changed
        $stmt = $this->pdo->prepare("SELECT password FROM test_reset_users WHERE email = ?");
        $stmt->execute(['test_change@example.com']);
        $hash = $stmt->fetchColumn();
        
        $this->assertTrue(password_verify('newpassword123', $hash));
    }

    public function testResetPasswordDeletesToken(): void
    {
        $this->createTestUser('test_delete@example.com');
        
        $result = $this->crud->requestPasswordReset('test_delete@example.com');
        $token = $result['token'];
        
        $this->crud->resetPassword($token, 'newpassword123');
        
        // Token should be deleted
        $email = $this->crud->validateResetToken($token);
        $this->assertNull($email);
    }

    public function testResetPasswordFailsWithInvalidToken(): void
    {
        $result = $this->crud->resetPassword('invalid_token', 'newpassword123');
        
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Invalid or expired', $result['error']);
    }

    public function testPasswordResetsTableIsCreatedAutomatically(): void
    {
        $this->createTestUser('test_auto@example.com');
        
        // Drop table if exists
        $this->pdo->exec("DROP TABLE IF EXISTS password_resets");
        
        // Request reset should create table
        $result = $this->crud->requestPasswordReset('test_auto@example.com');
        
        $this->assertTrue($result['success']);
        
        // Verify table exists
        $stmt = $this->pdo->query("SHOW TABLES LIKE 'password_resets'");
        $this->assertGreaterThan(0, $stmt->rowCount());
    }

    public function testMultipleResetRequestsForSameUser(): void
    {
        $this->createTestUser('test_multiple@example.com');
        
        $result1 = $this->crud->requestPasswordReset('test_multiple@example.com');
        $result2 = $this->crud->requestPasswordReset('test_multiple@example.com');
        
        $this->assertTrue($result1['success']);
        $this->assertTrue($result2['success']);
        $this->assertNotEquals($result1['token'], $result2['token']);
        
        // Both tokens should be valid
        $this->assertNotNull($this->crud->validateResetToken($result1['token']));
        $this->assertNotNull($this->crud->validateResetToken($result2['token']));
    }

    public function testResetPasswordWithExpiredTokenFails(): void
    {
        $this->createTestUser('test_expired_reset@example.com');
        
        $result = $this->crud->requestPasswordReset('test_expired_reset@example.com');
        $token = $result['token'];
        
        // Expire the token
        $this->pdo->exec("UPDATE password_resets SET expires_at = DATE_SUB(NOW(), INTERVAL 1 HOUR) WHERE token = '$token'");
        
        $result = $this->crud->resetPassword($token, 'newpassword123');
        
        $this->assertFalse($result['success']);
    }
}
