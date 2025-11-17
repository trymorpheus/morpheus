<?php

namespace Morpheus\Tests\CLI;

use PHPUnit\Framework\TestCase;
use Morpheus\CLI\Commands\DumpSQLCommand;

class DumpSQLCommandTest extends TestCase
{
    private \PDO $pdo;
    private string $testTable = 'test_dump_users';

    protected function setUp(): void
    {
        $this->pdo = \DynamicCRUD\Tests\TestHelper::getPDO();
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Create test table
        $this->pdo->exec("DROP TABLE IF EXISTS {$this->testTable}");
        $this->pdo->exec("
            CREATE TABLE {$this->testTable} (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255)
            ) COMMENT = '{\"display_name\": \"Test Users\", \"icon\": \"👤\"}'
        ");

        // Insert test data
        $this->pdo->exec("INSERT INTO {$this->testTable} (name, email) VALUES ('John', 'john@test.com')");
        $this->pdo->exec("INSERT INTO {$this->testTable} (name, email) VALUES ('Jane', 'jane@test.com')");
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS {$this->testTable}");
    }

    public function testDumpSQLWithOutput(): void
    {
        $command = new DumpSQLCommand();
        $outputFile = sys_get_temp_dir() . '/test_dump.sql';

        ob_start();
        $command->execute([$this->testTable, "--output=$outputFile"]);
        $output = ob_get_clean();

        $this->assertStringContainsString('SQL dump saved to', $output);
        $this->assertFileExists($outputFile);

        $dump = file_get_contents($outputFile);
        $this->assertStringContainsString('CREATE TABLE', $dump);
        $this->assertStringContainsString('INSERT INTO', $dump);
        $this->assertStringContainsString('John', $dump);
        $this->assertStringContainsString('jane@test.com', $dump);
        $this->assertStringContainsString('Test Users', $dump); // Metadata preserved

        unlink($outputFile);
    }

    public function testDumpSQLStructureOnly(): void
    {
        $command = new DumpSQLCommand();
        $outputFile = sys_get_temp_dir() . '/test_structure.sql';

        ob_start();
        $command->execute([$this->testTable, "--output=$outputFile", '--structure-only']);
        ob_get_clean();

        $dump = file_get_contents($outputFile);
        $this->assertStringContainsString('CREATE TABLE', $dump);
        $this->assertStringNotContainsString('INSERT INTO', $dump);

        unlink($outputFile);
    }

    public function testDumpSQLDataOnly(): void
    {
        $command = new DumpSQLCommand();
        $outputFile = sys_get_temp_dir() . '/test_data.sql';

        ob_start();
        $command->execute([$this->testTable, "--output=$outputFile", '--data-only']);
        ob_get_clean();

        $dump = file_get_contents($outputFile);
        $this->assertStringNotContainsString('CREATE TABLE', $dump);
        $this->assertStringContainsString('INSERT INTO', $dump);

        unlink($outputFile);
    }
}
