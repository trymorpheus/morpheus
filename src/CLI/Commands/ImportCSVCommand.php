<?php

namespace Morpheus\CLI\Commands;

use PDO;
use Morpheus\DynamicCRUD;

class ImportCSVCommand extends Command
{
    public function execute(array $args): void
    {
        if (empty($args[0]) || empty($args[1])) {
            $this->error('Table name and CSV file required');
            $this->info('Usage: php dynamiccrud import:csv <table> <file.csv> [--preview] [--skip-errors]');
            exit(1);
        }

        $table = $args[0];
        $file = $args[1];
        $preview = $this->hasOption($args, '--preview');
        $skipErrors = $this->hasOption($args, '--skip-errors');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            exit(1);
        }

        $pdo = $this->getPDO();
        $crud = new Morpheus($pdo, $table);
        
        $csv = file_get_contents($file);
        $result = $crud->import($csv, [
            'preview' => $preview,
            'skip_errors' => $skipErrors
        ]);

        if ($preview) {
            $this->info("Preview mode - no data imported");
            echo "  Rows to import: " . count($result['details']) . "\n";
            
            foreach (array_slice($result['details'], 0, 5) as $detail) {
                echo "  Row {$detail['row']}: " . json_encode($detail['data']) . "\n";
            }
            
            if (count($result['details']) > 5) {
                echo "  ... and " . (count($result['details']) - 5) . " more rows\n";
            }
        } else {
            $this->success("Import completed");
            echo "  Success: {$result['success']}\n";
            echo "  Errors: {$result['errors']}\n";
            echo "  Skipped: {$result['skipped']}\n";
            
            if ($result['errors'] > 0) {
                echo "\nErrors:\n";
                foreach ($result['details'] as $detail) {
                    if ($detail['status'] === 'error' || $detail['status'] === 'validation_error') {
                        echo "  Row {$detail['row']}: " . ($detail['message'] ?? json_encode($detail['errors'])) . "\n";
                    }
                }
            }
        }
    }

    private function hasOption(array $args, string $name): bool
    {
        return in_array($name, $args);
    }
}
