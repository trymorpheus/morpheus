<?php

namespace Morpheus\Export;

use PDO;

class ExportManager
{
    private PDO $pdo;
    private string $table;
    private array $schema;

    public function __construct(PDO $pdo, string $table, array $schema)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->schema = $schema;
    }

    public function toCSV(array $options = []): string
    {
        $data = $this->fetchData($options);
        $columns = $this->getExportColumns($options);
        
        $output = fopen('php://temp', 'r+');
        
        fputcsv($output, $columns, ',', '"', '');
        
        foreach ($data as $row) {
            $values = [];
            foreach ($columns as $col) {
                $values[] = $row[$col] ?? '';
            }
            fputcsv($output, $values, ',', '"', '');
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    public function toArray(array $options = []): array
    {
        return $this->fetchData($options);
    }

    private function fetchData(array $options): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (isset($options['where']) && is_array($options['where'])) {
            $conditions = [];
            foreach ($options['where'] as $col => $val) {
                $conditions[] = "$col = :$col";
                $params[$col] = $val;
            }
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        if (isset($options['order'])) {
            $sql .= " ORDER BY {$options['order']}";
        }
        
        if (isset($options['limit'])) {
            $sql .= " LIMIT {$options['limit']}";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getExportColumns(array $options): array
    {
        if (isset($options['columns'])) {
            return $options['columns'];
        }
        
        return array_map(fn($col) => $col['name'], $this->schema['columns']);
    }

    public function downloadCSV(string $filename, array $options = []): void
    {
        $csv = $this->toCSV($options);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csv));
        
        echo $csv;
        exit;
    }
}
