<?php

namespace DynamicCRUD\CLI;

class Application
{
    private array $commands = [];
    
    public function __construct()
    {
        $this->registerCommands();
    }
    
    private function registerCommands(): void
    {
        $this->commands = [
            'init' => new Commands\InitCommand(),
            'generate:metadata' => new Commands\GenerateMetadataCommand(),
            'validate:metadata' => new Commands\ValidateMetadataCommand(),
            'clear:cache' => new Commands\ClearCacheCommand(),
            'list:tables' => new Commands\ListTablesCommand(),
        ];
    }
    
    public function run(array $argv): void
    {
        array_shift($argv); // Remove script name
        
        if (empty($argv) || in_array($argv[0], ['--help', '-h', 'help'])) {
            $this->showHelp();
            return;
        }
        
        $command = $argv[0];
        $args = array_slice($argv, 1);
        
        if (!isset($this->commands[$command])) {
            $this->error("Unknown command: $command");
            $this->showHelp();
            exit(1);
        }
        
        try {
            $this->commands[$command]->execute($args);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            exit(1);
        }
    }
    
    private function showHelp(): void
    {
        echo "\n";
        echo "🚀 DynamicCRUD CLI Tool\n";
        echo "=======================\n\n";
        echo "Usage:\n";
        echo "  php dynamiccrud <command> [options]\n\n";
        echo "Available Commands:\n";
        echo "  init                    Initialize DynamicCRUD in current project\n";
        echo "  generate:metadata       Generate metadata JSON from table schema\n";
        echo "  validate:metadata       Validate table metadata JSON\n";
        echo "  clear:cache            Clear schema and template cache\n";
        echo "  list:tables            List all tables with metadata info\n\n";
        echo "Examples:\n";
        echo "  php dynamiccrud init\n";
        echo "  php dynamiccrud generate:metadata users\n";
        echo "  php dynamiccrud validate:metadata posts\n";
        echo "  php dynamiccrud clear:cache\n";
        echo "  php dynamiccrud list:tables\n\n";
    }
    
    private function error(string $message): void
    {
        echo "❌ Error: $message\n";
    }
}
