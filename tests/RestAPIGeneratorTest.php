<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\API\RestAPIGenerator;
use PDO;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class RestAPIGeneratorTest extends TestCase
{
    private PDO $pdo;
    private RestAPIGenerator $api;

    protected function setUp(): void
    {
        $this->pdo = TestHelper::getPDO();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS api_test_users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            password VARCHAR(255) NOT NULL
        )");
        
        $this->cleanupTestData();
        
        $this->api = new RestAPIGenerator($this->pdo, 'test-secret-key');
    }

    protected function tearDown(): void
    {
        $this->cleanupTestData();
        $this->pdo->exec("DROP TABLE IF EXISTS api_test_users");
    }

    private function cleanupTestData(): void
    {
        $this->pdo->exec("DELETE FROM api_test_users WHERE email LIKE 'test_%@example.com'");
    }

    public function testGenerateJWT(): void
    {
        $user = ['id' => 1, 'email' => 'test@example.com'];
        
        $reflection = new \ReflectionClass($this->api);
        $method = $reflection->getMethod('generateJWT');
        $method->setAccessible(true);
        
        $token = $method->invoke($this->api, $user);
        
        $this->assertIsString($token);
        $this->assertStringContainsString('.', $token);
        
        $parts = explode('.', $token);
        $this->assertCount(3, $parts);
    }

    public function testVerifyJWT(): void
    {
        $user = ['id' => 1, 'email' => 'test@example.com'];
        
        $reflection = new \ReflectionClass($this->api);
        $generateMethod = $reflection->getMethod('generateJWT');
        $generateMethod->setAccessible(true);
        
        $token = $generateMethod->invoke($this->api, $user);
        
        $verifyMethod = $reflection->getMethod('verifyJWT');
        $verifyMethod->setAccessible(true);
        
        $decoded = $verifyMethod->invoke($this->api, $token);
        
        $this->assertIsArray($decoded);
        $this->assertEquals(1, $decoded['user_id']);
        $this->assertEquals('test@example.com', $decoded['email']);
    }

    public function testVerifyInvalidJWT(): void
    {
        $reflection = new \ReflectionClass($this->api);
        $method = $reflection->getMethod('verifyJWT');
        $method->setAccessible(true);
        
        $result = $method->invoke($this->api, 'invalid.token.here');
        
        $this->assertNull($result);
    }

    public function testGenerateOpenAPISpec(): void
    {
        $spec = $this->api->generateOpenAPISpec();
        
        $this->assertIsArray($spec);
        $this->assertEquals('3.0.0', $spec['openapi']);
        $this->assertArrayHasKey('info', $spec);
        $this->assertArrayHasKey('paths', $spec);
        $this->assertEquals('DynamicCRUD REST API', $spec['info']['title']);
    }

    public function testOpenAPISpecContainsTables(): void
    {
        $spec = $this->api->generateOpenAPISpec();
        
        $this->assertArrayHasKey('/api_test_users', $spec['paths']);
        $this->assertArrayHasKey('/api_test_users/{id}', $spec['paths']);
        
        $this->assertArrayHasKey('get', $spec['paths']['/api_test_users']);
        $this->assertArrayHasKey('post', $spec['paths']['/api_test_users']);
        $this->assertArrayHasKey('get', $spec['paths']['/api_test_users/{id}']);
        $this->assertArrayHasKey('put', $spec['paths']['/api_test_users/{id}']);
        $this->assertArrayHasKey('delete', $spec['paths']['/api_test_users/{id}']);
    }

    public function testSanitizeUser(): void
    {
        $user = [
            'id' => 1,
            'email' => 'test@example.com',
            'password' => 'hashed_password'
        ];
        
        $reflection = new \ReflectionClass($this->api);
        $method = $reflection->getMethod('sanitizeUser');
        $method->setAccessible(true);
        
        $sanitized = $method->invoke($this->api, $user);
        
        $this->assertArrayHasKey('id', $sanitized);
        $this->assertArrayHasKey('email', $sanitized);
        $this->assertArrayNotHasKey('password', $sanitized);
    }

    public function testJWTExpiration(): void
    {
        $user = ['id' => 1, 'email' => 'test@example.com'];
        
        $reflection = new \ReflectionClass($this->api);
        $generateMethod = $reflection->getMethod('generateJWT');
        $generateMethod->setAccessible(true);
        
        $token = $generateMethod->invoke($this->api, $user);
        
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode($parts[1]), true);
        
        $this->assertArrayHasKey('exp', $payload);
        $this->assertGreaterThan(time(), $payload['exp']);
        $this->assertLessThanOrEqual(time() + 86400, $payload['exp']);
    }
}
