<?php

namespace Morpheus\CLI\Commands;

use Morpheus\GlobalMetadata;

class ConfigDeleteCommand
{
    public function execute(array $args): void
    {
        if (empty($args[0])) {
            echo "❌ Error: Config key required\n";
            echo "Usage: php dynamiccrud config:delete <key>\n";
            exit(1);
        }

        $key = $args[0];

        try {
            $pdo = $this->getConnection();
            $config = new GlobalMetadata($pdo);
            
            if (!$config->has($key)) {
                echo "❌ Config key not found: $key\n";
                exit(1);
            }

            $config->delete($key);
            echo "✅ Config deleted: $key\n";

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
