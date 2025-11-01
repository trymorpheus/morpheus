<?php

namespace DynamicCRUD;

use PDO;

class AuditLogger
{
    private PDO $pdo;
    private string $tableName;
    private bool $enabled;
    private ?int $userId;
    
    public function __construct(PDO $pdo, string $tableName = 'audit_log', bool $enabled = true)
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
        $this->enabled = $enabled;
        $this->userId = $_SESSION['user_id'] ?? null;
    }
    
    public function setUserId(?int $userId): void
    {
        $this->userId = $userId;
    }
    
    public function logCreate(string $table, int $recordId, array $newValues): void
    {
        if (!$this->enabled) return;
        
        $this->log($table, $recordId, 'CREATE', null, $newValues);
    }
    
    public function logUpdate(string $table, int $recordId, array $oldValues, array $newValues): void
    {
        if (!$this->enabled) return;
        
        $this->log($table, $recordId, 'UPDATE', $oldValues, $newValues);
    }
    
    public function logDelete(string $table, int $recordId, array $oldValues): void
    {
        if (!$this->enabled) return;
        
        $this->log($table, $recordId, 'DELETE', $oldValues, null);
    }
    
    private function log(string $table, int $recordId, string $action, ?array $oldValues, ?array $newValues): void
    {
        $sql = sprintf(
            "INSERT INTO %s (table_name, record_id, action, user_id, user_ip, old_values, new_values) 
             VALUES (:table_name, :record_id, :action, :user_id, :user_ip, :old_values, :new_values)",
            $this->tableName
        );
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'table_name' => $table,
            'record_id' => $recordId,
            'action' => $action,
            'user_id' => $this->userId,
            'user_ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null
        ]);
    }
    
    public function getHistory(string $table, int $recordId): array
    {
        $sql = sprintf(
            "SELECT * FROM %s WHERE table_name = :table AND record_id = :id ORDER BY created_at DESC",
            $this->tableName
        );
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['table' => $table, 'id' => $recordId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
