<?php

namespace Morpheus\CLI\Commands;

class ImportSQLCommand
{
    public function execute(array $args): void
    {
        if (empty($args[0])) {
            echo "❌ Error: SQL file required\n";
            echo "Usage: php dynamiccrud import:sql <file.sql> [--force]\n";
            exit(1);
        }

        $file = $args[0];
        $force = in_array('--force', $args);

        if (!file_exists($file)) {
            echo "❌ Error: File not found: $file\n";
            exit(1);
        }

        try {
            $pdo = $this->getConnection();
            $sql = file_get_contents($file);

            if (!$force) {
                echo "⚠️  This will execute SQL statements from: $file\n";
                echo "Continue? (yes/no): ";
                $handle = fopen("php://stdin", "r");
                $line = trim(fgets($handle));
                fclose($handle);

                if ($line !== 'yes') {
                    echo "❌ Import cancelled\n";
                    exit(0);
                }
            }

            $this->executeSQLDump($pdo, $sql);
            echo "✅ SQL imported successfully from: $file\n";

        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    private function executeSQLDump(\PDO $pdo, string $sql): void
    {
        // Remove comments
        $sql = preg_replace('/^--.*$/m', '', $sql);
        
        // Split by semicolon (simple approach)
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($stmt) => !empty($stmt)
        );

        // DDL statements (CREATE, DROP, ALTER) cannot be in transactions
        // Execute each statement individually
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $isDDL = preg_match('/^\s*(CREATE|DROP|ALTER|TRUNCATE)/i', $statement);
                
                if ($isDDL) {
                    // Execute DDL without transaction
                    $pdo->exec($statement);
                } else {
                    // Execute DML in transaction
                    if (!$pdo->inTransaction()) {
                        $pdo->beginTransaction();
                    }
                    $pdo->exec($statement);
                }
            }
        }
        
        // Commit any pending transaction
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
    }

    private function getConnection(): \PDO
    {
        $config = $this->loadConfig();
        
        $dsn = sprintf(
            '%s:host=%s;dbname=%s',
            $config['driver'] ?? 'mysql',
            $config['host'] ?? 'localhost',
            $config['database'] ?? 'test'
        );

        return new \PDO(
            $dsn,
            $config['username'] ?? 'root',
            $config['password'] ?? '',
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    }

    private function loadConfig(): array
    {
        $configFile = getcwd() . '/dynamiccrud.json';
        
        if (!file_exists($configFile)) {
            return [
                'driver' => 'mysql',
                'host' => 'localhost',
                'database' => 'test',
                'username' => 'root',
                'password' => 'rootpassword'
            ];
        }

        return json_decode(file_get_contents($configFile), true);
    }
}
