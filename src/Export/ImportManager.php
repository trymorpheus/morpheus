<?php

namespace Morpheus\Export;

use PDO;
use Morpheus\ValidationEngine;

class ImportManager
{
    private PDO $pdo;
    private string $table;
    private array $schema;
    private ?ValidationEngine $validator;

    public function __construct(PDO $pdo, string $table, array $schema)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->schema = $schema;
        $this->validator = new ValidationEngine($schema);
    }

    public function fromCSV(string $csvContent, array $options = []): array
    {
        $lines = str_getcsv($csvContent, "\n", '"', '');
        $headers = str_getcsv(array_shift($lines), ',', '"', '');
        
        $results = [
            'success' => 0,
            'errors' => 0,
            'skipped' => 0,
            'details' => []
        ];
        
        $preview = $options['preview'] ?? false;
        $skipErrors = $options['skip_errors'] ?? false;
        
        foreach ($lines as $index => $line) {
            if (empty(trim($line))) {
                $results['skipped']++;
                continue;
            }
            
            $values = str_getcsv($line, ',', '"', '');
            $row = array_combine($headers, $values);
            
            if ($preview) {
                $results['details'][] = ['row' => $index + 2, 'data' => $row, 'status' => 'preview'];
                continue;
            }
            
            if ($this->validator->validate($row)) {
                try {
                    $this->insertRow($row);
                    $results['success']++;
                    $results['details'][] = ['row' => $index + 2, 'status' => 'success'];
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['details'][] = ['row' => $index + 2, 'status' => 'error', 'message' => $e->getMessage()];
                    if (!$skipErrors) {
                        break;
                    }
                }
            } else {
                $results['errors']++;
                $results['details'][] = ['row' => $index + 2, 'status' => 'validation_error', 'errors' => $this->validator->getErrors()];
                if (!$skipErrors) {
                    break;
                }
            }
        }
        
        return $results;
    }

    private function insertRow(array $data): void
    {
        $pk = $this->schema['primary_key'];
        $columns = array_filter(array_keys($data), fn($col) => $col !== $pk);
        $placeholders = array_map(fn($col) => ":$col", $columns);
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($columns as $col) {
            $stmt->bindValue(":$col", $data[$col] ?? null);
        }
        
        $stmt->execute();
    }

    public function generateTemplate(): string
    {
        $columns = array_map(fn($col) => $col['name'], 
            array_filter($this->schema['columns'], fn($col) => !$col['is_primary'])
        );
        
        $output = fopen('php://temp', 'r+');
        fputcsv($output, $columns, ',', '"', '');
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}
