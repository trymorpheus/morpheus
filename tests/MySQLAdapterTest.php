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
        // Create temporary tables with FK
        try {
            $this->pdo->exec("CREATE TABLE test_fk_parent (id INT PRIMARY KEY)");
            $this->pdo->exec("CREATE TABLE test_fk_child (id INT PRIMARY KEY, parent_id INT, FOREIGN KEY (parent_id) REFERENCES test_fk_parent(id))");
            
            $schema = $this->adapter->getTableSchema('test_fk_child');
            
            $this->assertArrayHasKey('foreign_keys', $schema);
            $this->assertIsArray($schema['foreign_keys']);
            $this->assertNotEmpty($schema['foreign_keys']);
            
            $firstFk = reset($schema['foreign_keys']);
            $this->assertArrayHasKey('table', $firstFk);
            $this->assertArrayHasKey('column', $firstFk);
        } finally {
            $this->pdo->exec("DROP TABLE IF EXISTS test_fk_child");
            $this->pdo->exec("DROP TABLE IF EXISTS test_fk_parent");
        }
    }

    public function testEnumValuesExtraction()
    {
        $tableName = 'test_enum_table';
        try {
            $this->pdo->exec("CREATE TABLE {$tableName} (id INT, status ENUM('active', 'inactive'))");

            $schema = $this->adapter->getTableSchema($tableName);

            $statusColumn = null;
            foreach ($schema['columns'] as $column) {
                if ($column['name'] === 'status') {
                    $statusColumn = $column;
                    break;
                }
            }

            $this->assertNotNull($statusColumn);
            $this->assertEquals('enum', $statusColumn['sql_type']);
            $this->assertIsArray($statusColumn['enum_values']);
            $this->assertEquals(['active', 'inactive'], $statusColumn['enum_values']);

        } finally {
            $this->pdo->exec("DROP TABLE IF EXISTS {$tableName}");
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
        // Create temporary table
        try {
            $this->pdo->exec("CREATE TABLE test_insert_id (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50))");
            $this->pdo->exec("INSERT INTO test_insert_id (name) VALUES ('Test')");
            
            $id = $this->adapter->getLastInsertId();
            
            $this->assertIsInt($id);
            $this->assertGreaterThan(0, $id);
        } finally {
            $this->pdo->exec("DROP TABLE IF EXISTS test_insert_id");
        }
    }

    public function testEmptyTableReturnsEmptyArray()
    {
        $schema = $this->adapter->getTableSchema('nonexistent_table');
        
        $this->assertIsArray($schema);
        $this->assertEmpty($schema);
    }
}
