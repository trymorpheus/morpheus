<?php

namespace DynamicCRUD;

use PDO;
use DynamicCRUD\Cache\CacheStrategy;

class SchemaAnalyzer
{
    private PDO $pdo;
    private string $database;
    private ?CacheStrategy $cache;
    private int $cacheTtl;

    public function __construct(PDO $pdo, ?CacheStrategy $cache = null, int $cacheTtl = 3600)
    {
        $this->pdo = $pdo;
        $this->database = $pdo->query('SELECT DATABASE()')->fetchColumn();
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
    }

    public function getTableSchema(string $table): array
    {
        $cacheKey = "schema_{$this->database}_{$table}";
        
        if ($this->cache) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }
        
        $columns = $this->getColumns($table);
        $primaryKey = $this->getPrimaryKey($table);
        $foreignKeys = $this->getForeignKeys($table);
        
        $schema = [
            'table' => $table,
            'columns' => $columns,
            'primary_key' => $primaryKey,
            'foreign_keys' => $foreignKeys
        ];
        
        if ($this->cache) {
            $this->cache->set($cacheKey, $schema, $this->cacheTtl);
        }
        
        return $schema;
    }

    public function invalidateCache(string $table): bool
    {
        if (!$this->cache) {
            return false;
        }
        
        $cacheKey = "schema_{$this->database}_{$table}";
        return $this->cache->invalidate($cacheKey);
    }

    private function getColumns(string $table): array
    {
        $sql = "SELECT 
                    COLUMN_NAME as name,
                    DATA_TYPE as type,
                    CHARACTER_MAXIMUM_LENGTH as max_length,
                    IS_NULLABLE as nullable,
                    COLUMN_DEFAULT as default_value,
                    COLUMN_KEY as key_type,
                    COLUMN_COMMENT as comment
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = :database 
                AND TABLE_NAME = :table
                ORDER BY ORDINAL_POSITION";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['database' => $this->database, 'table' => $table]);
        
        $columns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $this->normalizeColumn($row);
        }
        
        return $columns;
    }

    private function normalizeColumn(array $row): array
    {
        $metadata = $this->parseMetadata($row['comment']);
        
        return [
            'name' => $row['name'],
            'sql_type' => $row['type'],
            'max_length' => $row['max_length'],
            'is_nullable' => $row['nullable'] === 'YES',
            'default_value' => $row['default_value'],
            'is_primary' => $row['key_type'] === 'PRI',
            'metadata' => $metadata
        ];
    }

    private function parseMetadata(?string $comment): array
    {
        if (empty($comment)) {
            return [];
        }

        $decoded = json_decode($comment, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function getPrimaryKey(string $table): ?string
    {
        $sql = "SELECT COLUMN_NAME
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = :database 
                AND TABLE_NAME = :table
                AND COLUMN_KEY = 'PRI'
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['database' => $this->database, 'table' => $table]);
        
        return $stmt->fetchColumn() ?: null;
    }

    private function getForeignKeys(string $table): array
    {
        $sql = "SELECT 
                    kcu.COLUMN_NAME as column_name,
                    kcu.REFERENCED_TABLE_NAME as referenced_table,
                    kcu.REFERENCED_COLUMN_NAME as referenced_column
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
                WHERE kcu.TABLE_SCHEMA = :database
                AND kcu.TABLE_NAME = :table
                AND kcu.REFERENCED_TABLE_NAME IS NOT NULL";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['database' => $this->database, 'table' => $table]);
        
        $foreignKeys = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $foreignKeys[$row['column_name']] = [
                'table' => $row['referenced_table'],
                'column' => $row['referenced_column']
            ];
        }
        
        return $foreignKeys;
    }
}
