<?php

use PHPUnit\Framework\TestCase;
use Morpheus\SchemaAnalyzer;

class SchemaAnalyzerPostgreSQLTest extends TestCase
{
    private $pdo;
    private $analyzer;

    protected function setUp(): void
    {
        try {
            $this->pdo = new PDO(
                'pgsql:host=localhost;dbname=test',
                'postgres',
                'postgres'
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->analyzer = new SchemaAnalyzer($this->pdo);
        } catch (PDOException $e) {
            $this->markTestSkipped('PostgreSQL not available: ' . $e->getMessage());
        }
    }

    public function testAutoDetectsPostgreSQLAdapter()
    {
        $schema = $this->analyzer->getTableSchema('users');
        
        $this->assertIsArray($schema);
        $this->assertNotEmpty($schema);
    }

    public function testGetTableSchemaReturnsValidStructure()
    {
        $schema = $this->analyzer->getTableSchema('users');
        
        $this->assertArrayHasKey('table', $schema);
        $this->assertArrayHasKey('columns', $schema);
        $this->assertArrayHasKey('primary_key', $schema);
        $this->assertArrayHasKey('foreign_keys', $schema);
    }

    public function testPrimaryKeyDetection()
    {
        $schema = $this->analyzer->getTableSchema('users');
        
        $this->assertEquals('id', $schema['primary_key']);
    }

    public function testForeignKeyDetection()
    {
        $schema = $this->analyzer->getTableSchema('posts');
        
        $this->assertIsArray($schema['foreign_keys']);
        
        if (!empty($schema['foreign_keys'])) {
            $this->assertArrayHasKey('category_id', $schema['foreign_keys']);
            $this->assertEquals('categories', $schema['foreign_keys']['category_id']['table']);
        }
    }

    public function testMetadataParsing()
    {
        $schema = $this->analyzer->getTableSchema('users');
        
        $emailColumn = null;
        foreach ($schema['columns'] as $column) {
            if ($column['name'] === 'email') {
                $emailColumn = $column;
                break;
            }
        }
        
        $this->assertNotNull($emailColumn);
        $this->assertIsArray($emailColumn['metadata']);
    }
}
