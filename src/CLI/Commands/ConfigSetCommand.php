<?php

namespace DynamicCRUD\CLI\Commands;

use DynamicCRUD\GlobalMetadata;

class ConfigSetCommand
{
    public function execute(array $args): void
    {
        if (count($args) < 2) {
            echo "❌ Error: Config key and value required\n";
            echo "Usage: php dynamiccrud config:set <key> <value>\n";
            echo "Example: php dynamiccrud config:set application.name \"My App\"\n";
            exit(1);
        }

        $key = $args[0];
        $value = $args[1];

        // Try to decode as JSON, otherwise use as string
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $value = $decoded;
        }

        try {
            $pdo = $this->getConnection();
            $config = new GlobalMetadata($pdo);
            
            $config->set($key, $value);
            
            echo "✅ Config set: $key\n";

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
