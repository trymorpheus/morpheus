<?php

namespace Morpheus\Database;

use PDO;

class PostgreSQLAdapter implements DatabaseAdapter
{
    private PDO $pdo;
    private string $schema;

    public function __construct(PDO $pdo, string $schema = 'public')
    {
        $this->pdo = $pdo;
        $this->schema = $schema;
    }

    public function getTableSchema(string $table): array
    {
        $sql = "SELECT 
            c.column_name as name,
            c.data_type as sql_type,
            c.is_nullable as is_nullable,
            c.column_default as default_value,
            c.character_maximum_length as max_length,
            pgd.description as comment,
            CASE WHEN pk.column_name IS NOT NULL THEN 'PRI' ELSE '' END as column_key
        FROM information_schema.columns c
        LEFT JOIN pg_catalog.pg_statio_all_tables st ON c.table_name = st.relname
        LEFT JOIN pg_catalog.pg_description pgd ON pgd.objoid = st.relid AND pgd.objsubid = c.ordinal_position
        LEFT JOIN (
            SELECT ku.column_name
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage ku ON tc.constraint_name = ku.constraint_name
            WHERE tc.constraint_type = 'PRIMARY KEY' 
            AND tc.table_schema = :schema 
            AND tc.table_name = :table
        ) pk ON c.column_name = pk.column_name
        WHERE c.table_schema = :schema AND c.table_name = :table
        ORDER BY c.ordinal_position";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['schema' => $this->schema, 'table' => $table]);
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

            $sqlType = $this->normalizeSqlType($column['sql_type']);
            $enumValues = [];
            
            if ($sqlType === 'enum') {
                $enumValues = $this->getEnumValues($table, $column['name']);
            }

            $processedColumns[] = [
                'name' => $column['name'],
                'sql_type' => $sqlType,
                'is_nullable' => $column['is_nullable'] === 'YES',
                'is_primary' => $isPrimary,
                'default_value' => $this->normalizeDefault($column['default_value']),
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
            kcu.column_name,
            ccu.table_name AS referenced_table,
            ccu.column_name AS referenced_column
        FROM information_schema.table_constraints tc
        JOIN information_schema.key_column_usage kcu ON tc.constraint_name = kcu.constraint_name
        JOIN information_schema.constraint_column_usage ccu ON ccu.constraint_name = tc.constraint_name
        WHERE tc.constraint_type = 'FOREIGN KEY'
        AND tc.table_schema = :schema
        AND tc.table_name = :table";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['schema' => $this->schema, 'table' => $table]);
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
        // PostgreSQL no tiene ENUM nativo como MySQL
        // Buscar CHECK constraints que simulen ENUM
        $sql = "SELECT pg_get_constraintdef(c.oid) as constraint_def
        FROM pg_constraint c
        JOIN pg_namespace n ON n.oid = c.connamespace
        JOIN pg_class cl ON cl.oid = c.conrelid
        WHERE c.contype = 'c'
        AND n.nspname = :schema
        AND cl.relname = :table
        AND pg_get_constraintdef(c.oid) LIKE '%' || :column || '%'";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'schema' => $this->schema,
            'table' => $table,
            'column' => $column
        ]);

        $constraint = $stmt->fetchColumn();

        if (!$constraint) {
            return [];
        }

        // Extraer valores del CHECK constraint
        // Ejemplo: CHECK ((status)::text = ANY ((ARRAY['active'::character varying, 'inactive'::character varying])::text[]))
        preg_match_all("/'([^']+)'/", $constraint, $matches);
        return $matches[1] ?? [];
    }

    public function quote(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    public function getLastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    private function normalizeSqlType(string $pgType): string
    {
        return match($pgType) {
            'character varying', 'varchar' => 'varchar',
            'character', 'char' => 'char',
            'integer', 'int4' => 'int',
            'bigint', 'int8' => 'bigint',
            'smallint', 'int2' => 'smallint',
            'timestamp without time zone', 'timestamp' => 'timestamp',
            'timestamp with time zone', 'timestamptz' => 'timestamp',
            'double precision', 'float8' => 'double',
            'real', 'float4' => 'float',
            'boolean', 'bool' => 'boolean',
            default => $pgType
        };
    }

    private function normalizeDefault($default): ?string
    {
        if ($default === null) {
            return null;
        }

        // PostgreSQL defaults vienen con casting: nextval('seq'::regclass)
        // Remover casting y funciones
        if (str_contains($default, 'nextval')) {
            return null; // Auto-increment
        }

        // Remover ::type casting
        $default = preg_replace('/::[a-z_]+/', '', $default);
        
        // Remover comillas simples
        $default = trim($default, "'");

        return $default;
    }
}
