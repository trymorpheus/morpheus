<?php

namespace Morpheus\Database;

use PDO;

class MySQLAdapter implements DatabaseAdapter
{
    private PDO $pdo;
    private string $database;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->database = $this->pdo->query('SELECT DATABASE()')->fetchColumn();
    }

    public function getTableSchema(string $table): array
    {
        $sql = "SELECT 
            COLUMN_NAME as name,
            DATA_TYPE as sql_type,
            IS_NULLABLE as is_nullable,
            COLUMN_KEY as column_key,
            COLUMN_DEFAULT as default_value,
            CHARACTER_MAXIMUM_LENGTH as max_length,
            COLUMN_COMMENT as comment,
            COLUMN_TYPE as column_type
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table
        ORDER BY ORDINAL_POSITION";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['database' => $this->database, 'table' => $table]);
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($columns)) {
            return [];
        }

        $primaryKey = null;
        $processedColumns = [];

        foreach ($columns as $column) {
            $isPrimary = $column['column_key'] === 'PRI';
            if ($isPrimary) {
                $primaryKey = $column['name'];
            }

            $metadata = [];
            if (!empty($column['comment'])) {
                $decoded = json_decode($column['comment'], true);
                $metadata = is_array($decoded) ? $decoded : [];
            }

            $enumValues = [];
            if ($column['sql_type'] === 'enum') {
                $enumValues = $this->getEnumValues($table, $column['name']);
            }

            $processedColumns[] = [
                'name' => $column['name'],
                'sql_type' => $column['sql_type'],
                'is_nullable' => $column['is_nullable'] === 'YES',
                'is_primary' => $isPrimary,
                'default_value' => $column['default_value'],
                'max_length' => $column['max_length'],
                'metadata' => $metadata,
                'enum_values' => $enumValues
            ];
        }

        return [
            'table' => $table,
            'primary_key' => $primaryKey,
            'columns' => $processedColumns,
            'foreign_keys' => $this->getForeignKeys($table)
        ];
    }

    public function getForeignKeys(string $table): array
    {
        $sql = "SELECT 
            COLUMN_NAME as column_name,
            REFERENCED_TABLE_NAME as referenced_table,
            REFERENCED_COLUMN_NAME as referenced_column
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = :database 
        AND TABLE_NAME = :table 
        AND REFERENCED_TABLE_NAME IS NOT NULL";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['database' => $this->database, 'table' => $table]);
        $fks = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $foreignKeys = [];
        foreach ($fks as $fk) {
            $foreignKeys[$fk['column_name']] = [
                'table' => $fk['referenced_table'],
                'column' => $fk['referenced_column']
            ];
        }

        return $foreignKeys;
    }

    public function getEnumValues(string $table, string $column): array
    {
        $sql = "SELECT COLUMN_TYPE 
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = :database 
        AND TABLE_NAME = :table 
        AND COLUMN_NAME = :column";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'database' => $this->database,
            'table' => $table,
            'column' => $column
        ]);

        $columnType = $stmt->fetchColumn();

        if (!$columnType || !str_starts_with($columnType, 'enum(')) {
            return [];
        }

        preg_match_all("/'([^']+)'/", $columnType, $matches);
        return $matches[1] ?? [];
    }

    public function quote(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    public function getLastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }
}
