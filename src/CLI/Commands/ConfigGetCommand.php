<?php

namespace DynamicCRUD\CLI\Commands;

use DynamicCRUD\GlobalMetadata;

class ConfigGetCommand
{
    public function execute(array $args): void
    {
        if (empty($args[0])) {
            echo "❌ Error: Config key required\n";
            echo "Usage: php dynamiccrud config:get <key>\n";
            exit(1);
        }

        $key = $args[0];

        try {
            $pdo = $this->getConnection();
            $config = new GlobalMetadata($pdo);
            
            $value = $config->get($key);
            
            if ($value === null) {
                echo "❌ Config key not found: $key\n";
                exit(1);
            }

            echo json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

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
