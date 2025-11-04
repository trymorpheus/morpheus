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
            'test:webhook' => new Commands\TestWebhookCommand(),
            'webhook:configure' => new Commands\ConfigureWebhookCommand(),
            'metadata:export' => new Commands\ExportMetadataCommand(),
            'metadata:import' => new Commands\ImportMetadataCommand(),
            'test:connection' => new Commands\TestConnectionCommand(),
            'export:csv' => new Commands\ExportCSVCommand(),
            'import:csv' => new Commands\ImportCSVCommand(),
            'generate:template' => new Commands\GenerateTemplateCommand(),
            'dump:sql' => new Commands\DumpSQLCommand(),
            'import:sql' => new Commands\ImportSQLCommand(),
            'config:get' => new Commands\ConfigGetCommand(),
            'config:set' => new Commands\ConfigSetCommand(),
            'config:list' => new Commands\ConfigListCommand(),
            'config:delete' => new Commands\ConfigDeleteCommand(),
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
        echo "  list:tables            List all tables with metadata info\n";
        echo "  clear:cache            Clear schema and template cache\n";
        echo "  test:connection        Test database connection\n\n";
        echo "Metadata Commands:\n";
        echo "  generate:metadata      Generate metadata JSON from table schema\n";
        echo "  validate:metadata      Validate table metadata JSON\n";
        echo "  metadata:export        Export table metadata to JSON file\n";
        echo "  metadata:import        Import table metadata from JSON file\n\n";
        echo "Webhook Commands:\n";
        echo "  webhook:configure      Configure webhook for a table\n";
        echo "  test:webhook           Test webhook connectivity\n\n";
        echo "Export/Import Commands:\n";
        echo "  export:csv             Export table data to CSV\n";
        echo "  import:csv             Import data from CSV file\n";
        echo "  generate:template      Generate CSV import template\n";
        echo "  dump:sql               Export table structure and data to SQL\n";
        echo "  import:sql             Import SQL dump file\n\n";
        echo "Global Config Commands:\n";
        echo "  config:get             Get global configuration value\n";
        echo "  config:set             Set global configuration value\n";
        echo "  config:list            List all global configuration\n";
        echo "  config:delete          Delete global configuration key\n\n";
        echo "Examples:\n";
        echo "  php dynamiccrud init\n";
        echo "  php dynamiccrud list:tables\n";
        echo "  php dynamiccrud test:connection\n";
        echo "  php dynamiccrud generate:metadata users\n";
        echo "  php dynamiccrud metadata:export users --output=users.json\n";
        echo "  php dynamiccrud metadata:import users.json\n";
        echo "  php dynamiccrud webhook:configure users https://webhook.site/abc123\n";
        echo "  php dynamiccrud test:webhook users\n";
        echo "  php dynamiccrud export:csv users --output=users.csv\n";
        echo "  php dynamiccrud import:csv users data.csv --preview\n";
        echo "  php dynamiccrud generate:template users --output=template.csv\n";
        echo "  php dynamiccrud dump:sql users --output=users.sql\n";
        echo "  php dynamiccrud import:sql backup.sql --force\n";
        echo "  php dynamiccrud config:set application.name \"My App\"\n";
        echo "  php dynamiccrud config:get application.name\n";
        echo "  php dynamiccrud config:list\n";
        echo "  php dynamiccrud clear:cache\n\n";
    }
    
    private function error(string $message): void
    {
        echo "❌ Error: $message\n";
    }
}
