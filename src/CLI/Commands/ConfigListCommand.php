<?php

namespace DynamicCRUD\CLI\Commands;

use DynamicCRUD\GlobalMetadata;

class ConfigListCommand
{
    public function execute(array $args): void
    {
        try {
            $pdo = $this->getConnection();
            $config = new GlobalMetadata($pdo);
            
            $all = $config->all();
            
            if (empty($all)) {
                echo "ℹ️  No configuration found\n";
                return;
            }

            echo "📋 Global Configuration:\n\n";
            
            foreach ($all as $key => $value) {
                echo "  $key:\n";
                $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $lines = explode("\n", $json);
                foreach ($lines as $line) {
                    echo "    $line\n";
                }
                echo "\n";
            }

        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            exit(1);
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
