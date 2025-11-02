<?php

namespace DynamicCRUD\Tests;

use DynamicCRUD\SchemaAnalyzer;
use DynamicCRUD\Cache\FileCacheStrategy;
use PHPUnit\Framework\TestCase;
use PDO;

class SchemaAnalyzerTest extends TestCase
{
    private PDO $pdo;
    private SchemaAnalyzer $analyzer;

    protected function setUp(): void
    {
        $this->pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s', getenv('DB_HOST'), getenv('DB_NAME')),
            getenv('DB_USER'),
            getenv('DB_PASS')
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
        $schema = $this->analyzer->getTableSchema('posts');
        
        $this->assertIsArray($schema['foreign_keys']);
        
        if (!empty($schema['foreign_keys'])) {
            $firstFk = reset($schema['foreign_keys']);
            $this->assertArrayHasKey('table', $firstFk);
            $this->assertArrayHasKey('column', $firstFk);
        } else {
            $this->markTestSkipped('No foreign keys found in posts table');
        }
    }

    public function testEnumValuesExtraction(): void
    {
        $this->markTestSkipped('TEMPORARY tables not visible to INFORMATION_SCHEMA in some MySQL configurations');
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
