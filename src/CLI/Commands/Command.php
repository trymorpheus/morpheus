<?php

namespace DynamicCRUD\CLI\Commands;

abstract class Command
{
    abstract public function execute(array $args): void;
    
    protected function success(string $message): void
    {
        echo "✅ $message\n";
    }
    
    protected function info(string $message): void
    {
        echo "ℹ️  $message\n";
    }
    
    protected function warning(string $message): void
    {
        echo "⚠️  $message\n";
    }
    
    protected function error(string $message): void
    {
        echo "❌ $message\n";
    }
    
    protected function getPDO(): \PDO
    {
        $config = $this->loadConfig();
        $db = $config['database'] ?? $config;

        $driver   = $db['driver']   ?? getenv('DB_DRIVER') ?: 'mysql';
        $host     = $db['host']     ?? getenv('DB_HOST')   ?: 'localhost';
        $dbName   = $db['database'] ?? getenv('DB_NAME')   ?: 'test';
        $user     = $db['username'] ?? getenv('DB_USER')   ?: 'root';
        $pass     = $db['password'] ?? getenv('DB_PASS')   ?: '';

        $dsn = sprintf(
            '%s:host=%s;dbname=%s',
            $driver,
            $host,
            $dbName
        );

        $pdo = new \PDO($dsn, $user, $pass);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        return $pdo;
    }
    
    protected function loadConfig(): array
    {
        $configFile = getcwd() . '/dynamiccrud.json';
        
        if (!file_exists($configFile)) {
            return [];
        }
        
        $config = json_decode(file_get_contents($configFile), true);
        
        if (!is_array($config)) {
            throw new \Exception('Invalid configuration file');
        }
        
        return $config;
    }

    protected function getOption(array $args, string $option): ?string
    {
        foreach ($args as $arg) {
            if (strpos($arg, $option . '=') === 0) {
                return substr($arg, strlen($option) + 1);
            }
        }
        return null;
    }
}
