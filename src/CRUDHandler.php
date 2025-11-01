<?php

namespace DynamicCRUD;

use PDO;
use DynamicCRUD\Cache\CacheStrategy;

class CRUDHandler
{
    private PDO $pdo;
    private string $table;
    private SchemaAnalyzer $analyzer;
    private SecurityModule $security;
    private array $schema;

    public function __construct(PDO $pdo, string $table, ?CacheStrategy $cache = null)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->analyzer = new SchemaAnalyzer($pdo, $cache);
        $this->security = new SecurityModule();
        $this->schema = $this->analyzer->getTableSchema($table);
    }

    public function renderForm(?int $id = null): string
    {
        $data = [];
        
        if ($id !== null) {
            $data = $this->findById($id);
        }
        
        $csrfToken = $this->security->generateCsrfToken();
        $generator = new FormGenerator($this->schema, $data, $csrfToken, $this->pdo);
        
        return $generator->render();
    }

    public function handleSubmission(): array
    {
        $csrfToken = $_POST['_csrf_token'] ?? '';
        
        if (!$this->security->validateCsrfToken($csrfToken)) {
            return ['success' => false, 'error' => 'Token CSRF inválido'];
        }
        
        $allowedColumns = array_map(
            fn($col) => $col['name'],
            array_filter($this->schema['columns'], fn($col) => !$col['is_primary'])
        );
        
        $data = $this->security->sanitizeInput($_POST, $allowedColumns);
        
        $validator = new ValidationEngine($this->schema);
        
        if (!$validator->validate($data)) {
            return ['success' => false, 'errors' => $validator->getErrors()];
        }
        
        $id = isset($_POST['id']) && $_POST['id'] ? $this->update((int)$_POST['id'], $data) : $this->save($data);
        
        return ['success' => true, 'id' => $id];
    }

    private function save(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);
        
        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        return (int) $this->pdo->lastInsertId();
    }

    private function update(int $id, array $data): int
    {
        $pk = $this->schema['primary_key'];
        $sets = array_map(fn($col) => "{$col} = :{$col}", array_keys($data));
        
        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = :id",
            $this->table,
            implode(', ', $sets),
            $pk
        );
        
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        
        return $id;
    }

    private function findById(int $id): array
    {
        $pk = $this->schema['primary_key'];
        
        $sql = sprintf("SELECT * FROM %s WHERE %s = :id LIMIT 1", $this->table, $pk);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function list(array $options = []): array
    {
        $listGenerator = new ListGenerator($this->pdo, $this->schema);
        return $listGenerator->list($options);
    }

    public function delete(int $id): bool
    {
        $pk = $this->schema['primary_key'];
        
        $sql = sprintf("DELETE FROM %s WHERE %s = :id", $this->table, $pk);
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute(['id' => $id]);
    }
}
