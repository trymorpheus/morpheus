<?php

namespace Morpheus\Tests;

use Morpheus\FormGenerator;
use PHPUnit\Framework\TestCase;

class FormGeneratorTest extends TestCase
{
    private array $schema;

    protected function setUp(): void
    {
        $this->schema = [
            'table' => 'users',
            'primary_key' => 'id',
            'columns' => [
                ['name' => 'id', 'sql_type' => 'int', 'is_primary' => true, 'is_nullable' => false, 'max_length' => null, 'default_value' => null, 'metadata' => []],
                ['name' => 'name', 'sql_type' => 'varchar', 'is_primary' => false, 'is_nullable' => false, 'max_length' => 100, 'default_value' => null, 'metadata' => []],
                ['name' => 'email', 'sql_type' => 'varchar', 'is_primary' => false, 'is_nullable' => false, 'max_length' => 255, 'default_value' => null, 'metadata' => ['type' => 'email']],
                ['name' => 'age', 'sql_type' => 'int', 'is_primary' => false, 'is_nullable' => true, 'max_length' => null, 'default_value' => null, 'metadata' => []],
            ],
            'foreign_keys' => []
        ];
    }

    public function testRenderBasicForm(): void
    {
        $generator = new FormGenerator($this->schema, [], 'test_token');
        $html = $generator->render();

        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('method="POST"', $html);
        $this->assertStringContainsString('</form>', $html);
    }

    public function testRenderCsrfToken(): void
    {
        $generator = new FormGenerator($this->schema, [], 'my_csrf_token');
        $html = $generator->render();

        $this->assertStringContainsString('name="csrf_token"', $html);
        $this->assertStringContainsString('value="my_csrf_token"', $html);
        $this->assertStringContainsString('type="hidden"', $html);
    }

    public function testRenderTextInput(): void
    {
        $generator = new FormGenerator($this->schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('name="name"', $html);
        $this->assertStringContainsString('type="text"', $html);
        $this->assertStringContainsString('maxlength="100"', $html);
    }

    public function testRenderEmailInput(): void
    {
        $generator = new FormGenerator($this->schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('name="email"', $html);
        $this->assertStringContainsString('type="email"', $html);
    }

    public function testRenderNumberInput(): void
    {
        $generator = new FormGenerator($this->schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('name="age"', $html);
        $this->assertStringContainsString('type="number"', $html);
    }

    public function testRenderRequiredAttribute(): void
    {
        $generator = new FormGenerator($this->schema, []);
        $html = $generator->render();

        $this->assertMatchesRegularExpression('/name="name"[^>]*required/', $html);
        $this->assertMatchesRegularExpression('/name="email"[^>]*required/', $html);
    }

    public function testRenderWithExistingData(): void
    {
        $data = ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'];
        $generator = new FormGenerator($this->schema, $data);
        $html = $generator->render();

        $this->assertStringContainsString('value="John Doe"', $html);
        $this->assertStringContainsString('value="john@example.com"', $html);
        $this->assertStringContainsString('name="id" value="1"', $html);
    }

    public function testRenderHiddenIdField(): void
    {
        $data = ['id' => 5];
        $generator = new FormGenerator($this->schema, $data);
        $html = $generator->render();

        $this->assertStringContainsString('<input type="hidden" name="id" value="5">', $html);
    }

    public function testRenderSubmitButton(): void
    {
        $generator = new FormGenerator($this->schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('<button type="submit"', $html);
        $this->assertStringContainsString('Guardar</button>', $html);
    }

    public function testRenderLabels(): void
    {
        $generator = new FormGenerator($this->schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('<label for="name">Name</label>', $html);
        $this->assertStringContainsString('<label for="email">Email</label>', $html);
    }

    public function testRenderCustomLabel(): void
    {
        $schema = $this->schema;
        $schema['columns'][1]['metadata'] = ['label' => 'Full Name'];
        
        $generator = new FormGenerator($schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('Full Name', $html);
    }

    public function testRenderTooltip(): void
    {
        $schema = $this->schema;
        $schema['columns'][2]['metadata'] = ['type' => 'email', 'tooltip' => 'Enter your email address'];
        
        $generator = new FormGenerator($schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('Enter your email address', $html);
        $this->assertStringContainsString('tooltip', $html);
    }

    public function testRenderMinMaxAttributes(): void
    {
        $schema = $this->schema;
        $schema['columns'][3]['metadata'] = ['min' => 18, 'max' => 120];
        
        $generator = new FormGenerator($schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('min="18"', $html);
        $this->assertStringContainsString('max="120"', $html);
    }

    public function testRenderMinLengthAttribute(): void
    {
        $schema = $this->schema;
        $schema['columns'][1]['metadata'] = ['minlength' => 3];
        
        $generator = new FormGenerator($schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('minlength="3"', $html);
    }

    public function testRenderTextarea(): void
    {
        $schema = $this->schema;
        $schema['columns'][] = [
            'name' => 'description',
            'sql_type' => 'text',
            'is_primary' => false,
            'is_nullable' => true,
            'max_length' => null,
            'default_value' => null,
            'metadata' => []
        ];
        
        $generator = new FormGenerator($schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringContainsString('name="description"', $html);
    }

    public function testRenderEnumSelect(): void
    {
        $schema = $this->schema;
        $schema['columns'][] = [
            'name' => 'status',
            'sql_type' => 'enum',
            'is_primary' => false,
            'is_nullable' => false,
            'max_length' => null,
            'default_value' => null,
            'enum_values' => ['active', 'inactive', 'pending'],
            'metadata' => []
        ];
        
        $generator = new FormGenerator($schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('<select name="status"', $html);
        $this->assertStringContainsString('<option value="active"', $html);
        $this->assertStringContainsString('<option value="inactive"', $html);
        $this->assertStringContainsString('<option value="pending"', $html);
    }

    public function testRenderEnumWithSelectedValue(): void
    {
        $schema = $this->schema;
        $schema['columns'][] = [
            'name' => 'status',
            'sql_type' => 'enum',
            'is_primary' => false,
            'is_nullable' => false,
            'max_length' => null,
            'default_value' => null,
            'enum_values' => ['active', 'inactive'],
            'metadata' => []
        ];
        
        $data = ['status' => 'active'];
        $generator = new FormGenerator($schema, $data);
        $html = $generator->render();

        $this->assertMatchesRegularExpression('/<option value="active"[^>]*selected/', $html);
    }

    public function testRenderHiddenField(): void
    {
        $schema = $this->schema;
        $schema['columns'][] = [
            'name' => 'secret',
            'sql_type' => 'varchar',
            'is_primary' => false,
            'is_nullable' => true,
            'max_length' => 255,
            'default_value' => null,
            'metadata' => ['hidden' => true]
        ];
        
        $generator = new FormGenerator($schema, []);
        $html = $generator->render();

        $this->assertStringNotContainsString('name="secret"', $html);
    }

    public function testRenderFileInput(): void
    {
        $schema = $this->schema;
        $schema['columns'][] = [
            'name' => 'avatar',
            'sql_type' => 'varchar',
            'is_primary' => false,
            'is_nullable' => true,
            'max_length' => 255,
            'default_value' => null,
            'metadata' => ['type' => 'file', 'accept' => 'image/*']
        ];
        
        $generator = new FormGenerator($schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('name="avatar"', $html);
        $this->assertStringContainsString('accept="image/*"', $html);
    }

    public function testRenderFormWithFileFieldHasEnctype(): void
    {
        $schema = $this->schema;
        $schema['columns'][] = [
            'name' => 'document',
            'sql_type' => 'varchar',
            'is_primary' => false,
            'is_nullable' => true,
            'max_length' => 255,
            'default_value' => null,
            'metadata' => ['type' => 'file']
        ];
        
        $generator = new FormGenerator($schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('enctype="multipart/form-data"', $html);
    }

    public function testRenderIncludesAssets(): void
    {
        $generator = new FormGenerator($this->schema, []);
        $html = $generator->render();

        $this->assertStringContainsString('<style>', $html);
        $this->assertStringContainsString('.dynamic-crud-form', $html);
    }

    public function testHtmlEscaping(): void
    {
        $data = ['name' => '<script>alert("xss")</script>'];
        $generator = new FormGenerator($this->schema, $data);
        $html = $generator->render();

        // Check that the XSS attempt is escaped in the input value
        $this->assertStringContainsString('value="&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;"', $html);
        // Ensure the dangerous script is not executable
        $this->assertStringNotContainsString('value="<script>alert("xss")</script>"', $html);
    }
}
