<?php

namespace Morpheus\Tests;

use Morpheus\SecurityModule;
use PHPUnit\Framework\TestCase;

class SecurityModuleTest extends TestCase
{
    private SecurityModule $security;

    protected function setUp(): void
    {
        $this->security = new SecurityModule();
    }

    public function testSanitizeInputTrimsAndFilters(): void
    {
        $schema = [
            'columns' => [
                ['name' => 'name', 'is_nullable' => false],
                ['name' => 'description', 'is_nullable' => false]
            ]
        ];
        
        $input = [
            'name' => '  John Doe  ',
            'description' => '  Some text  '
        ];
        
        $sanitized = $this->security->sanitizeInput($input, ['name', 'description'], $schema);
        
        $this->assertEquals('John Doe', $sanitized['name']);
        $this->assertEquals('Some text', $sanitized['description']);
    }

    public function testSanitizeInputConvertsEmptyToNull(): void
    {
        $schema = [
            'columns' => [
                ['name' => 'optional_field', 'is_nullable' => true]
            ]
        ];
        
        $input = ['optional_field' => ''];
        
        $sanitized = $this->security->sanitizeInput($input, ['optional_field'], $schema);
        
        $this->assertNull($sanitized['optional_field']);
    }

    public function testSanitizeInputKeepsEmptyForNonNullable(): void
    {
        $schema = [
            'columns' => [
                ['name' => 'required_field', 'is_nullable' => false]
            ]
        ];
        
        $input = ['required_field' => ''];
        
        $sanitized = $this->security->sanitizeInput($input, ['required_field'], $schema);
        
        $this->assertEquals('', $sanitized['required_field']);
    }

    public function testSanitizeInputFiltersAllowedColumns(): void
    {
        $schema = [
            'columns' => [
                ['name' => 'name', 'is_nullable' => false],
                ['name' => 'email', 'is_nullable' => false]
            ]
        ];
        
        $input = [
            'name' => 'John',
            'email' => 'john@example.com',
            'malicious_field' => 'hacker'
        ];
        
        $sanitized = $this->security->sanitizeInput($input, ['name', 'email'], $schema);
        
        $this->assertArrayHasKey('name', $sanitized);
        $this->assertArrayHasKey('email', $sanitized);
        $this->assertArrayNotHasKey('malicious_field', $sanitized);
    }

    public function testSanitizeInputTrimsWhitespace(): void
    {
        $schema = [
            'columns' => [
                ['name' => 'name', 'is_nullable' => false]
            ]
        ];
        
        $input = ['name' => '  John Doe  '];
        
        $sanitized = $this->security->sanitizeInput($input, ['name'], $schema);
        
        $this->assertEquals('John Doe', $sanitized['name']);
    }

    public function testEscapeOutput(): void
    {
        $input = '<script>alert("xss")</script>';
        $escaped = $this->security->escapeOutput($input);
        
        $this->assertStringContainsString('&lt;', $escaped);
        $this->assertStringContainsString('&gt;', $escaped);
        $this->assertStringNotContainsString('<script>', $escaped);
    }
}
