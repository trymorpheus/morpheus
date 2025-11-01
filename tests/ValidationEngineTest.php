<?php

namespace DynamicCRUD\Tests;

use DynamicCRUD\ValidationEngine;

class ValidationEngineTest
{
    private array $schema;
    
    public function __construct()
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
    
    public function testRequiredFields(): void
    {
        $validator = new ValidationEngine($this->schema);
        $result = $validator->validate(['age' => 25]);
        
        assert($result === false, 'Validación debe fallar sin campos requeridos');
        assert(!empty($validator->getErrors()['name']), 'Debe haber error en campo name');
        
        echo "✓ testRequiredFields pasó\n";
    }
    
    public function testEmailValidation(): void
    {
        $validator = new ValidationEngine($this->schema);
        $result = $validator->validate([
            'name' => 'Test',
            'email' => 'invalid-email'
        ]);
        
        assert($result === false, 'Validación debe fallar con email inválido');
        assert(!empty($validator->getErrors()['email']), 'Debe haber error en campo email');
        
        echo "✓ testEmailValidation pasó\n";
    }
    
    public function testUrlValidation(): void
    {
        $validator = new ValidationEngine($this->schema);
        $result = $validator->validate([
            'name' => 'Test',
            'email' => 'test@example.com',
            'website' => 'not-a-url'
        ]);
        
        assert($result === false, 'Validación debe fallar con URL inválida');
        assert(!empty($validator->getErrors()['website']), 'Debe haber error en campo website');
        
        echo "✓ testUrlValidation pasó\n";
    }
    
    public function testValidData(): void
    {
        $validator = new ValidationEngine($this->schema);
        $result = $validator->validate([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'age' => 30,
            'website' => 'https://example.com'
        ]);
        
        assert($result === true, 'Validación debe pasar con datos válidos');
        assert(empty($validator->getErrors()), 'No debe haber errores');
        
        echo "✓ testValidData pasó\n";
    }
    
    public function run(): void
    {
        echo "Ejecutando tests de ValidationEngine...\n\n";
        $this->testRequiredFields();
        $this->testEmailValidation();
        $this->testUrlValidation();
        $this->testValidData();
        echo "\n✓ Todos los tests pasaron\n";
    }
}

if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $test = new ValidationEngineTest();
    $test->run();
}
