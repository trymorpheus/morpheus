<?php

namespace DynamicCRUD\CLI\Commands;

use DynamicCRUD\SchemaAnalyzer;

class GenerateMetadataCommand extends Command
{
    public function execute(array $args): void
    {
        if (empty($args[0])) {
            $this->error('Table name required');
            echo "Usage: php dynamiccrud generate:metadata <table>\n";
            exit(1);
        }
        
        $table = $args[0];
        $pdo = $this->getPDO();
        
        $this->info("Generating metadata for table: $table");
        
        $analyzer = new SchemaAnalyzer($pdo);
        $schema = $analyzer->getTableSchema($table);
        
        $metadata = $this->generateMetadata($schema);
        
        $sql = $this->generateAlterStatement($table, $metadata);
        
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "Generated Metadata:\n";
        echo str_repeat('=', 60) . "\n\n";
        echo json_encode($metadata, JSON_PRETTY_PRINT) . "\n\n";
        echo str_repeat('=', 60) . "\n";
        echo "SQL Statement:\n";
        echo str_repeat('=', 60) . "\n\n";
        echo $sql . "\n\n";
        
        $this->success('Metadata generated successfully');
        $this->info('Copy the SQL statement above and run it on your database');
    }
    
    private function generateMetadata(array $schema): array
    {
        $metadata = [
            'display_name' => ucfirst(str_replace('_', ' ', $schema['table'])),
            'icon' => '📄',
        ];
        
        // Detect common patterns
        $columns = array_map(fn($col) => $col['name'], $schema['columns']);
        
        if (in_array('created_at', $columns) || in_array('updated_at', $columns)) {
            $metadata['behaviors']['timestamps'] = [];
            if (in_array('created_at', $columns)) {
                $metadata['behaviors']['timestamps']['created_at'] = 'created_at';
            }
            if (in_array('updated_at', $columns)) {
                $metadata['behaviors']['timestamps']['updated_at'] = 'updated_at';
            }
        }
        
        if (in_array('slug', $columns) && in_array('title', $columns)) {
            $metadata['behaviors']['sluggable'] = [
                'source' => 'title',
                'target' => 'slug',
            ];
        }
        
        if (in_array('deleted_at', $columns)) {
            $metadata['behaviors']['soft_deletes'] = [
                'enabled' => true,
                'column' => 'deleted_at',
            ];
        }
        
        $searchable = [];
        foreach ($schema['columns'] as $col) {
            if (in_array($col['sql_type'], ['varchar', 'text', 'longtext'])) {
                $searchable[] = $col['name'];
            }
        }
        
        if (!empty($searchable)) {
            $metadata['list_view']['searchable'] = array_slice($searchable, 0, 3);
        }
        
        return $metadata;
    }
    
    private function generateAlterStatement(string $table, array $metadata): string
    {
        $json = json_encode($metadata, JSON_UNESCAPED_UNICODE);
        $json = str_replace("'", "''", $json);
        
        return "ALTER TABLE $table COMMENT = '$json';";
    }
}
