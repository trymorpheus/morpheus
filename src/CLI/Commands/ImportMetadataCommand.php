<?php

namespace DynamicCRUD\CLI\Commands;

use PDO;

class ImportMetadataCommand extends Command
{
    public function execute(array $args): void
    {
        if (empty($args[0])) {
            $this->error('JSON file required');
            $this->info('Usage: php dynamiccrud metadata:import <file.json>');
            exit(1);
        }

        $file = $args[0];

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            exit(1);
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (!$data || !isset($data['table'])) {
            $this->error('Invalid metadata file format');
            exit(1);
        }

        $table = $data['table'];
        $pdo = $this->getPDO();

        // Check table exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
        $stmt->execute([$table]);
        if ($stmt->fetchColumn() == 0) {
            $this->error("Table '$table' not found");
            exit(1);
        }

        // Import table metadata
        if (isset($data['table_metadata'])) {
            $comment = json_encode($data['table_metadata']);
            $sql = "ALTER TABLE `$table` COMMENT = " . $pdo->quote($comment);
            $pdo->exec($sql);
            $this->success("Table metadata imported");
        }

        // Import column metadata
        if (isset($data['column_metadata'])) {
            foreach ($data['column_metadata'] as $column => $metadata) {
                $comment = json_encode($metadata);
                $sql = "ALTER TABLE `$table` MODIFY COLUMN `$column` " . 
                       $this->getColumnDefinition($pdo, $table, $column) . 
                       " COMMENT " . $pdo->quote($comment);
                $pdo->exec($sql);
            }
            $this->success("Column metadata imported (" . count($data['column_metadata']) . " columns)");
        }

        $this->success("Metadata import completed for table '$table'");
    }

    private function getColumnDefinition(PDO $pdo, string $table, string $column): string
    {
        $stmt = $pdo->prepare("SELECT COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
        $stmt->execute([$table, $column]);
        $col = $stmt->fetch(PDO::FETCH_ASSOC);

        $def = $col['COLUMN_TYPE'];
        $def .= $col['IS_NULLABLE'] === 'YES' ? ' NULL' : ' NOT NULL';
        if ($col['COLUMN_DEFAULT'] !== null) {
            $def .= " DEFAULT " . $pdo->quote($col['COLUMN_DEFAULT']);
        }

        return $def;
    }
}
