<?php

namespace DynamicCRUD;

class GlobalMetadata
{
    private \PDO $pdo;
    private array $cache = [];
    private string $tableName = '_dynamiccrud_config';

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->ensureTableExists();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $stmt = $this->pdo->prepare("SELECT config_value FROM {$this->tableName} WHERE config_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();

        if ($result === false) {
            return $default;
        }

        $value = json_decode($result, true);
        $this->cache[$key] = $value;
        return $value;
    }

    public function set(string $key, mixed $value): void
    {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO {$this->tableName} (config_key, config_value, updated_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE config_value = ?, updated_at = NOW()
        ");
        $stmt->execute([$key, $json, $json]);
        $this->cache[$key] = $value;
    }

    public function has(string $key): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->tableName} WHERE config_key = ?");
        $stmt->execute([$key]);
        return (bool) $stmt->fetchColumn();
    }

    public function delete(string $key): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$this->tableName} WHERE config_key = ?");
        $stmt->execute([$key]);
        unset($this->cache[$key]);
        return $stmt->rowCount() > 0;
    }

    public function all(): array
    {
        $stmt = $this->pdo->query("SELECT config_key, config_value FROM {$this->tableName} ORDER BY config_key");
        $result = [];
        
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $result[$row['config_key']] = json_decode($row['config_value'], true);
        }
        
        return $result;
    }

    public function clear(): void
    {
        $this->pdo->exec("TRUNCATE TABLE {$this->tableName}");
        $this->cache = [];
    }

    private function ensureTableExists(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS {$this->tableName} (
                id INT PRIMARY KEY AUTO_INCREMENT,
                config_key VARCHAR(255) UNIQUE NOT NULL,
                config_value JSON NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_config_key (config_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }
}
