<?php

namespace DynamicCRUD\CLI\Commands;

class InitCommand extends Command
{
    public function execute(array $args): void
    {
        $this->info('Initializing DynamicCRUD...');
        
        $configFile = getcwd() . '/dynamiccrud.json';
        
        if (file_exists($configFile)) {
            $this->warning('Configuration file already exists');
            echo "Overwrite? (y/n): ";
            $answer = trim(fgets(STDIN));
            if (strtolower($answer) !== 'y') {
                $this->info('Initialization cancelled');
                return;
            }
        }
        
        $config = $this->promptConfig();
        
        file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
        
        $this->success('Configuration file created: dynamiccrud.json');
        $this->info('You can now use other commands');
    }
    
    private function promptConfig(): array
    {
        echo "\nDatabase Configuration:\n";
        echo "======================\n\n";
        
        echo "Driver (mysql/pgsql) [mysql]: ";
        $driver = trim(fgets(STDIN)) ?: 'mysql';
        
        echo "Host [localhost]: ";
        $host = trim(fgets(STDIN)) ?: 'localhost';
        
        echo "Database name [test]: ";
        $database = trim(fgets(STDIN)) ?: 'test';
        
        echo "Username [root]: ";
        $username = trim(fgets(STDIN)) ?: 'root';
        
        echo "Password []: ";
        $password = trim(fgets(STDIN));
        
        return [
            'driver' => $driver,
            'host' => $host,
            'database' => $database,
            'username' => $username,
            'password' => $password,
        ];
    }
}
