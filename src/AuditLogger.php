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
        if (!$this->isEnabled()) return;
        $this->log($table, $recordId, 'create', null, $newValues);
    }
    
    public function logUpdate(string $table, int $recordId, array $oldValues, array $newValues): void
    {
        if (!$this->isEnabled()) return;
        $this->log($table, $recordId, 'update', $oldValues, $newValues);
    }
    
    public function logDelete(string $table, int $recordId, array $oldValues): void
    {
        if (!$this->isEnabled()) return;
        $this->log($table, $recordId, 'delete', $oldValues, null);
    }
    
    private function isEnabled(): bool
    {
        return $this->enabled;
    }
    
    private function log(string $table, int $recordId, string $action, ?array $oldValues, ?array $newValues): void
    {
        $sql = $this->buildInsertSql();
        $params = $this->prepareLogParams($table, $recordId, $action, $oldValues, $newValues);
        $this->executeLog($sql, $params);
    }
    
    private function buildInsertSql(): string
    {
        return sprintf(
            "INSERT INTO %s (table_name, record_id, action, user_id, ip_address, old_values, new_values) 
             VALUES (:table_name, :record_id, :action, :user_id, :ip_address, :old_values, :new_values)",
            $this->tableName
        );
    }
    
    private function prepareLogParams(string $table, int $recordId, string $action, ?array $oldValues, ?array $newValues): array
    {
        return [
            'table_name' => $table,
            'record_id' => $recordId,
            'action' => $action,
            'user_id' => $this->userId,
            'ip_address' => $this->getIpAddress(),
            'old_values' => $this->encodeValues($oldValues),
            'new_values' => $this->encodeValues($newValues)
        ];
    }
    
    private function getIpAddress(): ?string
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }
    
    private function encodeValues(?array $values): ?string
    {
        return $values ? json_encode($values) : null;
    }
    
    private function executeLog(string $sql, array $params): void
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
    
    public function getHistory(string $table, int $recordId): array
    {
        $sql = $this->buildHistorySql();
        return $this->fetchHistory($sql, $table, $recordId);
    }
    
    private function buildHistorySql(): string
    {
        return sprintf(
            "SELECT * FROM %s WHERE table_name = :table AND record_id = :id ORDER BY created_at DESC",
            $this->tableName
        );
    }
    
    private function fetchHistory(string $sql, string $table, int $recordId): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['table' => $table, 'id' => $recordId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
