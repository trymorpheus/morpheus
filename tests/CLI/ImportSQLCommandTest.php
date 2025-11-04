<?php

namespace DynamicCRUD\Tests\CLI;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\CLI\Commands\ImportSQLCommand;

class ImportSQLCommandTest extends TestCase
{
    private \PDO $pdo;
    private string $testTable = 'test_import_products';

    protected function setUp(): void
    {
        $this->pdo = new \PDO('mysql:host=127.0.0.1;port=3306;dbname=test', 'root', 'rootpassword');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->cleanUp();
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    private function cleanUp(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS {$this->testTable}");
    }

    public function testImportSQLWithForce(): void
    {
        $sqlFile = sys_get_temp_dir() . '/test_import.sql';
        $sql = "
            DROP TABLE IF EXISTS {$this->testTable};
            CREATE TABLE {$this->testTable} (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                price DECIMAL(10,2)
            ) COMMENT = '{\"display_name\": \"Products\"}';
            
            INSERT INTO {$this->testTable} (name, price) VALUES ('Product A', 19.99);
            INSERT INTO {$this->testTable} (name, price) VALUES ('Product B', 29.99);
        ";
        file_put_contents($sqlFile, $sql);

        $command = new ImportSQLCommand();

        ob_start();
        $command->execute([$sqlFile, '--force']);
        $output = ob_get_clean();

        $this->assertStringContainsString('SQL imported successfully', $output);

        // Verify data
        $stmt = $this->pdo->query("SELECT * FROM {$this->testTable}");
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->assertCount(2, $rows);
        $this->assertEquals('Product A', $rows[0]['name']);
        $this->assertEquals('29.99', $rows[1]['price']);

        unlink($sqlFile);
    }
}
