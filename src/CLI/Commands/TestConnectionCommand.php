<?php

namespace DynamicCRUD\CLI\Commands;

use PDO;

class TestConnectionCommand extends Command
{
    public function execute(array $args): void
    {
        $this->info("Testing database connection...");

        try {
            $pdo = $this->getPDO();
            
            // Test connection
            $version = $pdo->query('SELECT VERSION()')->fetchColumn();
            $database = $pdo->query('SELECT DATABASE()')->fetchColumn();
            
            $this->success("✓ Connection successful");
            echo "  Database: $database\n";
            echo "  Version: $version\n";
            
            // Count tables
            $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()");
            $tableCount = $stmt->fetchColumn();
            echo "  Tables: $tableCount\n";
            
        } catch (\Exception $e) {
            $this->error("Connection failed: " . $e->getMessage());
            exit(1);
        }
    }
}
