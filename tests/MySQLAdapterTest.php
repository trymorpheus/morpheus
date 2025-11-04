<?php

use PHPUnit\Framework\TestCase;
use DynamicCRUD\Database\MySQLAdapter;

class MySQLAdapterTest extends TestCase
{
    private $pdo;
    private $adapter;

    protected function setUp(): void
    {
        $this->pdo = \DynamicCRUD\Tests\TestHelper::getPDO();
        $this->adapter = new MySQLAdapter($this->pdo);
    }

    public function testGetTableSchema()
    {
        $schema = $this->adapter->getTableSchema('users');
        
        $this->assertIsArray($schema);
        $this->assertArrayHasKey('table', $schema);
        $this->assertArrayHasKey('primary_key', $schema);
        $this->assertArrayHasKey('columns', $schema);
        $this->assertArrayHasKey('foreign_keys', $schema);
        $this->assertEquals('users', $schema['table']);
    }

    public function testPrimaryKeyDetection()
    {
        $schema = $this->adapter->getTableSchema('users');
        
        $this->assertEquals('id', $schema['primary_key']);
    }

    public function testColumnStructure()
    {
        $schema = $this->adapter->getTableSchema('users');
        
        $this->assertNotEmpty($schema['columns']);
        
        $firstColumn = $schema['columns'][0];
        $this->assertArrayHasKey('name', $firstColumn);
        $this->assertArrayHasKey('sql_type', $firstColumn);
        $this->assertArrayHasKey('is_nullable', $firstColumn);
        $this->assertArrayHasKey('is_primary', $firstColumn);
    }

    public function testMetadataParsingFromComment()
    {
        $schema = $this->adapter->getTableSchema('users');
        
        $emailColumn = null;
        foreach ($schema['columns'] as $column) {
            if ($column['name'] === 'email') {
                $emailColumn = $column;
                break;
            }
        }
        
        $this->assertNotNull($emailColumn);
        $this->assertArrayHasKey('metadata', $emailColumn);
        $this->assertIsArray($emailColumn['metadata']);
    }

    public function testForeignKeyDetection()
    {
        $schema = $this->adapter->getTableSchema('posts');
        
        $this->assertArrayHasKey('foreign_keys', $schema);
        $this->assertIsArray($schema['foreign_keys']);
        
        if (!empty($schema['foreign_keys'])) {
            $firstFk = reset($schema['foreign_keys']);
            $this->assertArrayHasKey('table', $firstFk);
            $this->assertArrayHasKey('column', $firstFk);
        }
    }

    public function testEnumValuesExtraction()
    {
        $schema = $this->adapter->getTableSchema('users');
        
        // Buscar columna ENUM si existe
        $hasEnum = false;
        foreach ($schema['columns'] as $column) {
            if ($column['sql_type'] === 'enum' && !empty($column['enum_values'])) {
                $hasEnum = true;
                $this->assertIsArray($column['enum_values']);
                $this->assertNotEmpty($column['enum_values']);
                break;
            }
        }
        
        // Si no hay ENUM, marcar como skipped
        if (!$hasEnum) {
            $this->markTestSkipped('No ENUM columns in users table');
        }
    }

    public function testQuoteIdentifier()
    {
        $quoted = $this->adapter->quote('table_name');
        
        $this->assertEquals('`table_name`', $quoted);
    }

    public function testQuoteIdentifierWithSpecialChars()
    {
        $quoted = $this->adapter->quote('table`name');
        
        $this->assertEquals('`table``name`', $quoted);
    }

    public function testGetLastInsertId()
    {
        // Insertar en categories que tiene menos campos requeridos
        $this->pdo->exec("INSERT INTO categories (name, description) VALUES ('Test Category', 'Test')");
        
        $id = $this->adapter->getLastInsertId();
        
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
        
        // Cleanup
        $this->pdo->exec("DELETE FROM categories WHERE name = 'Test Category'");
    }

    public function testEmptyTableReturnsEmptyArray()
    {
        $schema = $this->adapter->getTableSchema('nonexistent_table');
        
        $this->assertIsArray($schema);
        $this->assertEmpty($schema);
    }
}
