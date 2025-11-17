<?php

namespace Morpheus\CLI\Commands;

use PDO;
use Morpheus\DynamicCRUD;

class ExportCSVCommand extends Command
{
    public function execute(array $args): void
    {
        if (empty($args[0])) {
            $this->error('Table name required');
            $this->info('Usage: php dynamiccrud export:csv <table> [--output=file.csv]');
            exit(1);
        }

        $table = $args[0];
        $output = $this->getOption($args, '--output');
        $pdo = $this->getPDO();

        $crud = new Morpheus($pdo, $table);
        $csv = $crud->export('csv');

        if ($output) {
            file_put_contents($output, $csv);
            $this->success("Data exported to: $output");
            
            $lines = substr_count($csv, "\n");
            echo "  Rows: " . ($lines - 1) . "\n";
        } else {
            echo $csv;
        }
    }

    protected function getOption(array $args, string $name): ?string
    {
        foreach ($args as $arg) {
            if (strpos($arg, $name . '=') === 0) {
                return substr($arg, strlen($name) + 1);
            }
        }
        return null;
    }
}
