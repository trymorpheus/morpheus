<?php

namespace Morpheus\Tests;

use Morpheus\ValidationEngine;
use PHPUnit\Framework\TestCase;

class ValidationEngineTest extends TestCase
{
    private array $schema;
    
    protected function setUp(): void
    {
        $this->schema = [
            'table' => 'users',
            'primary_key' => 'id',
            'columns' => [
                ['name' => 'name', 'sql_type' => 'varchar', 'max_length' => 100, 'is_nullable' => false, 'is_primary' => false, 'metadata' => []],
                ['name' => 'email', 'sql_type' => 'varchar', 'max_length' => 255, 'is_nullable' => false, 'is_primary' => false, 'metadata' => ['type' => 'email']],
                ['name' => 'age', 'sql_type' => 'int', 'max_length' => null, 'is_nullable' => true, 'is_primary' => false, 'metadata' => []],
                ['name' => 'website', 'sql_type' => 'varchar', 'max_length' => 255, 'is_nullable' => true, 'is_primary' => false, 'metadata' => ['type' => 'url']],
            ]
        ];
    }
    
    public function testRequiredFieldsValidationFails(): void
    {
        $validator = new ValidationEngine($this->schema);
        $result = $validator->validate(['age' => 25]);
        
        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $validator->getErrors());
        $this->assertArrayHasKey('email', $validator->getErrors());
    }
    
    public function testInvalidEmailValidationFails(): void
    {
        $validator = new ValidationEngine($this->schema);
        $result = $validator->validate([
            'name' => 'Test User',
            'email' => 'invalid-email'
        ]);
        
        $this->assertFalse($result);
        $this->assertArrayHasKey('email', $validator->getErrors());
    }
    
    public function testInvalidUrlValidationFails(): void
    {
        $validator = new ValidationEngine($this->schema);
        $result = $validator->validate([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'website' => 'not-a-url'
        ]);
        
        $this->assertFalse($result);
        $this->assertArrayHasKey('website', $validator->getErrors());
    }
    
    public function testValidDataPasses(): void
    {
        $validator = new ValidationEngine($this->schema);
        $result = $validator->validate([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'age' => 30,
            'website' => 'https://example.com'
        ]);
        
        $this->assertTrue($result);
        $this->assertEmpty($validator->getErrors());
    }
    
    public function testNullableFieldsAcceptNull(): void
    {
        $validator = new ValidationEngine($this->schema);
        $result = $validator->validate([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'age' => null,
            'website' => null
        ]);
        
        $this->assertTrue($result);
        $this->assertEmpty($validator->getErrors());
    }
    
    public function testMinMaxValidation(): void
    {
        $schema = $this->schema;
        $schema['columns'][] = [
            'name' => 'score',
            'sql_type' => 'int',
            'max_length' => null,
            'is_nullable' => false,
            'is_primary' => false,
            'metadata' => ['min' => 0, 'max' => 100]
        ];
        
        $validator = new ValidationEngine($schema);
        
        // Test below min
        $result = $validator->validate([
            'name' => 'Test',
            'email' => 'test@example.com',
            'score' => -1
        ]);
        $this->assertFalse($result);
        
        // Test above max
        $result = $validator->validate([
            'name' => 'Test',
            'email' => 'test@example.com',
            'score' => 101
        ]);
        $this->assertFalse($result);
        
        // Test valid range
        $result = $validator->validate([
            'name' => 'Test',
            'email' => 'test@example.com',
            'score' => 50
        ]);
        $this->assertTrue($result);
    }
    
    public function testMinLengthValidation(): void
    {
        $schema = $this->schema;
        $schema['columns'][0]['metadata'] = ['minlength' => 3];
        
        $validator = new ValidationEngine($schema);
        
        // Test too short
        $result = $validator->validate([
            'name' => 'AB',
            'email' => 'test@example.com'
        ]);
        $this->assertFalse($result);
        $this->assertArrayHasKey('name', $validator->getErrors());
        
        // Test valid length
        $result = $validator->validate([
            'name' => 'ABC',
            'email' => 'test@example.com'
        ]);
        $this->assertTrue($result);
    }
}
