<?php

namespace DynamicCRUD\Tests;

use DynamicCRUD\SchemaAnalyzer;
use PDO;

class SchemaAnalyzerTest
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function testGetTableSchema(): void
    {
        $analyzer = new SchemaAnalyzer($this->pdo);
        $schema = $analyzer->getTableSchema('users');

        assert(!empty($schema['columns']), 'Schema debe tener columnas');
        assert($schema['primary_key'] === 'id', 'Primary key debe ser id');
        assert($schema['table'] === 'users', 'Nombre de tabla debe ser users');

        echo "✓ testGetTableSchema pasó\n";
    }

    public function testParseMetadata(): void
    {
        $analyzer = new SchemaAnalyzer($this->pdo);
        $schema = $analyzer->getTableSchema('users');

        $emailColumn = null;
        foreach ($schema['columns'] as $col) {
            if ($col['name'] === 'email') {
                $emailColumn = $col;
                break;
            }
        }

        assert($emailColumn !== null, 'Columna email debe existir');
        assert($emailColumn['metadata']['type'] === 'email', 'Metadata type debe ser email');

        echo "✓ testParseMetadata pasó\n";
    }

    public function run(): void
    {
        echo "Ejecutando tests de SchemaAnalyzer...\n\n";
        $this->testGetTableSchema();
        $this->testParseMetadata();
        echo "\n✓ Todos los tests pasaron\n";
    }
}

// Ejecutar tests si se llama directamente
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $test = new SchemaAnalyzerTest();
    $test->run();
}
