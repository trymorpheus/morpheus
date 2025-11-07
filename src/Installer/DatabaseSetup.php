<?php

namespace DynamicCRUD\Installer;

use PDO;
use PDOException;

class DatabaseSetup
{
    private ?PDO $pdo = null;

    public function testConnection(string $host, string $database, string $username, string $password, string $driver = 'mysql'): array
    {
        try {
            $dsn = $driver === 'pgsql' 
                ? "pgsql:host=$host;dbname=$database" 
                : "mysql:host=$host;dbname=$database;charset=utf8mb4";

            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $this->pdo = $pdo;

            return [
                'success' => true,
                'message' => 'Database connection successful',
                'driver' => $driver,
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    public function createCoreTables(): array
    {
        if (!$this->pdo) {
            return ['success' => false, 'error' => 'No database connection'];
        }

        try {
            $this->pdo->beginTransaction();

            // Global config table
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS _dynamiccrud_config (
                    config_key VARCHAR(255) PRIMARY KEY,
                    config_value TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");

            // Users table
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS _users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    role VARCHAR(50) DEFAULT 'user',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) COMMENT '{\"display_name\":\"Users\",\"icon\":\"👥\",\"authentication\":{\"enabled\":true}}'
            ");

            $this->pdo->commit();

            return [
                'success' => true,
                'message' => 'Core tables created successfully',
                'tables' => ['_dynamiccrud_config', '_users'],
            ];
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function createAdminUser(string $name, string $email, string $password): array
    {
        if (!$this->pdo) {
            return ['success' => false, 'error' => 'No database connection'];
        }

        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $this->pdo->prepare("
                INSERT INTO _users (name, email, password, role) 
                VALUES (:name, :email, :password, 'admin')
            ");

            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'password' => $hashedPassword,
            ]);

            return [
                'success' => true,
                'message' => 'Admin user created successfully',
                'user_id' => (int) $this->pdo->lastInsertId(),
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function getPDO(): ?PDO
    {
        return $this->pdo;
    }
}
