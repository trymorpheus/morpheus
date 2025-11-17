<?php

namespace Morpheus\CLI\Commands;

use PDO;

class ExportMetadataCommand extends Command
{
    public function execute(array $args): void
    {
        if (empty($args[0])) {
            $this->error('Table name required');
            $this->info('Usage: php dynamiccrud metadata:export <table> [--output=file.json]');
            exit(1);
        }

        $table = $args[0];
        $output = $this->getOption($args, '--output');
        $pdo = $this->getPDO();

        // Get table metadata
        $stmt = $pdo->prepare("SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$table]);
        $comment = $stmt->fetchColumn();

        if ($comment === false) {
            $this->error("Table '$table' not found");
            exit(1);
        }

        $metadata = $comment ? json_decode(html_entity_decode($comment), true) : [];
        
        // Get column metadata
        $stmt = $pdo->prepare("SELECT COLUMN_NAME, COLUMN_COMMENT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? ORDER BY ORDINAL_POSITION");
        $stmt->execute([$table]);
        $columns = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($row['COLUMN_COMMENT']) {
                $columnMeta = json_decode(html_entity_decode($row['COLUMN_COMMENT']), true);
                if ($columnMeta) {
                    $columns[$row['COLUMN_NAME']] = $columnMeta;
                }
            }
        }

        $export = [
            'table' => $table,
            'table_metadata' => $metadata,
            'column_metadata' => $columns,
            'exported_at' => date('c')
        ];

        $json = json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if ($output) {
            file_put_contents($output, $json);
            $this->success("Metadata exported to: $output");
        } else {
            echo $json . "\n";
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
