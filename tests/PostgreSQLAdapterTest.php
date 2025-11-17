<?php

use PHPUnit\Framework\TestCase;
use Morpheus\Database\PostgreSQLAdapter;

class PostgreSQLAdapterTest extends TestCase
{
    private $pdo;
    private $adapter;

    protected function setUp(): void
    {
        try {
            $this->pdo = new PDO(
                'pgsql:host=localhost;dbname=test',
                'postgres',
                'postgres'
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->adapter = new PostgreSQLAdapter($this->pdo);
        } catch (PDOException $e) {
            $this->markTestSkipped('PostgreSQL not available: ' . $e->getMessage());
        }
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

    public function testQuoteIdentifier()
    {
        $quoted = $this->adapter->quote('table_name');
        
        $this->assertEquals('"table_name"', $quoted);
    }

    public function testQuoteIdentifierWithSpecialChars()
    {
        $quoted = $this->adapter->quote('table"name');
        
        $this->assertEquals('"table""name"', $quoted);
    }

    public function testTypeNormalization()
    {
        $schema = $this->adapter->getTableSchema('users');
        
        foreach ($schema['columns'] as $column) {
            // Verificar que los tipos están normalizados
            $this->assertIsString($column['sql_type']);
            $this->assertNotEquals('character varying', $column['sql_type']);
            $this->assertNotEquals('integer', $column['sql_type']);
        }
    }

    public function testGetLastInsertId()
    {
        $this->pdo->exec("INSERT INTO users (name, email, password, age) VALUES ('Test User', 'test@test.com', 'testpass', 25)");
        
        $id = $this->adapter->getLastInsertId();
        
        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);
        
        // Cleanup
        $this->pdo->exec("DELETE FROM users WHERE email = 'test@test.com'");
    }

    public function testEmptyTableReturnsEmptyArray()
    {
        $schema = $this->adapter->getTableSchema('nonexistent_table');
        
        $this->assertIsArray($schema);
        $this->assertEmpty($schema);
    }
}
