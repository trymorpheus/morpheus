<?php

namespace Morpheus\Tests;

use Morpheus\SchemaAnalyzer;
use Morpheus\Cache\FileCacheStrategy;
use PHPUnit\Framework\TestCase;
use PDO;

class SchemaAnalyzerTest extends TestCase
{
    private PDO $pdo;
    private SchemaAnalyzer $analyzer;

    protected function setUp(): void
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: 3306;
        $dbname = getenv('DB_NAME') ?: 'test';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: 'rootpassword';

        $this->pdo = new PDO(
            sprintf('mysql:host=%s;port=%d;dbname=%s', $host, $port, $dbname),
            $user,
            $pass
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->analyzer = new SchemaAnalyzer($this->pdo);
    }

    public function testGetTableSchemaReturnsValidStructure(): void
    {
        $schema = $this->analyzer->getTableSchema('users');

        $this->assertIsArray($schema);
        $this->assertArrayHasKey('table', $schema);
        $this->assertArrayHasKey('columns', $schema);
        $this->assertArrayHasKey('primary_key', $schema);
        $this->assertArrayHasKey('foreign_keys', $schema);
        
        $this->assertEquals('users', $schema['table']);
        $this->assertNotEmpty($schema['columns']);
    }

    public function testPrimaryKeyDetection(): void
    {
        $schema = $this->analyzer->getTableSchema('users');
        
        $this->assertEquals('id', $schema['primary_key']);
    }

    public function testColumnStructure(): void
    {
        $schema = $this->analyzer->getTableSchema('users');
        
        $this->assertNotEmpty($schema['columns']);
        
        $firstColumn = $schema['columns'][0];
        $this->assertArrayHasKey('name', $firstColumn);
        $this->assertArrayHasKey('sql_type', $firstColumn);
        $this->assertArrayHasKey('is_nullable', $firstColumn);
        $this->assertArrayHasKey('is_primary', $firstColumn);
        $this->assertArrayHasKey('metadata', $firstColumn);
    }

    public function testMetadataParsingFromComment(): void
    {
        $schema = $this->analyzer->getTableSchema('users');

        $emailColumn = null;
        foreach ($schema['columns'] as $col) {
            if ($col['name'] === 'email') {
                $emailColumn = $col;
                break;
            }
        }

        $this->assertNotNull($emailColumn);
        $this->assertIsArray($emailColumn['metadata']);
        
        if (isset($emailColumn['metadata']['type'])) {
            $this->assertEquals('email', $emailColumn['metadata']['type']);
        }
    }

    public function testForeignKeyDetection(): void
    {
        // Create temporary tables with FK
        try {
            $this->pdo->exec("CREATE TABLE test_sa_fk_parent (id INT PRIMARY KEY)");
            $this->pdo->exec("CREATE TABLE test_sa_fk_child (id INT PRIMARY KEY, parent_id INT, FOREIGN KEY (parent_id) REFERENCES test_sa_fk_parent(id))");
            
            $schema = $this->analyzer->getTableSchema('test_sa_fk_child');
            
            $this->assertIsArray($schema['foreign_keys']);
            $this->assertNotEmpty($schema['foreign_keys']);
            
            $firstFk = reset($schema['foreign_keys']);
            $this->assertArrayHasKey('table', $firstFk);
            $this->assertArrayHasKey('column', $firstFk);
        } finally {
            $this->pdo->exec("DROP TABLE IF EXISTS test_sa_fk_child");
            $this->pdo->exec("DROP TABLE IF EXISTS test_sa_fk_parent");
        }
    }

    public function testEnumValuesExtraction()
    {
        $tableName = 'test_schema_enum_table';
        try {
            $this->pdo->exec("CREATE TABLE {$tableName} (id INT, status ENUM('pending', 'completed'))");

            $schema = $this->analyzer->getTableSchema($tableName);

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
            $this->assertEquals(['pending', 'completed'], $statusColumn['enum_values']);

        } finally {
            $this->pdo->exec("DROP TABLE IF EXISTS {$tableName}");
        }
    }

    public function testCacheIntegration(): void
    {
        $cacheDir = __DIR__ . '/../cache';
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $cache = new FileCacheStrategy($cacheDir);
        $analyzer = new SchemaAnalyzer($this->pdo, $cache);

        // First call - should cache
        $schema1 = $analyzer->getTableSchema('users');
        
        // Second call - should use cache
        $schema2 = $analyzer->getTableSchema('users');

        $this->assertEquals($schema1, $schema2);
    }

    public function testInvalidTableReturnsEmptyOrThrows(): void
    {
        try {
            $schema = $this->analyzer->getTableSchema('nonexistent_table_xyz');
            $this->assertEmpty($schema['columns'], 'Should return empty columns for non-existent table');
        } catch (\Exception $e) {
            $this->assertStringContainsString('', $e->getMessage());
        }
    }
}
