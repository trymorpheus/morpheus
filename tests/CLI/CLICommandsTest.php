<?php

namespace DynamicCRUD\Tests\CLI;

use PHPUnit\Framework\TestCase;

class CLICommandsTest extends TestCase
{
    public function testTestConnectionCommand(): void
    {
        $output = shell_exec('php bin/dynamiccrud test:connection 2>&1');
        $this->assertStringContainsString('Connection successful', $output);
        $this->assertStringContainsString('Database:', $output);
    }
    
    public function testListTablesCommand(): void
    {
        $output = shell_exec('php bin/dynamiccrud list:tables 2>&1');
        $this->assertStringContainsString('tables found', $output);
    }
    
    public function testHelpCommand(): void
    {
        $output = shell_exec('php bin/dynamiccrud --help 2>&1');
        $this->assertStringContainsString('DynamicCRUD CLI Tool', $output);
        $this->assertStringContainsString('test:webhook', $output);
        $this->assertStringContainsString('metadata:export', $output);
    }
}
