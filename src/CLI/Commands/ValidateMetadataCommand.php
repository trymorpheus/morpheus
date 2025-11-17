<?php

namespace Morpheus\CLI\Commands;

use Morpheus\Metadata\TableMetadata;

class ValidateMetadataCommand extends Command
{
    public function execute(array $args): void
    {
        if (empty($args[0])) {
            $this->error('Table name required');
            echo "Usage: php dynamiccrud validate:metadata <table>\n";
            exit(1);
        }
        
        $table = $args[0];
        $pdo = $this->getPDO();
        
        $this->info("Validating metadata for table: $table");
        
        try {
            $metadata = new TableMetadata($pdo, $table);
            
            echo "\n" . str_repeat('=', 60) . "\n";
            echo "Metadata Validation Results:\n";
            echo str_repeat('=', 60) . "\n\n";
            
            $this->checkFeature('Display Name', $metadata->getDisplayName());
            $this->checkFeature('Icon', $metadata->getIcon());
            $this->checkFeature('Timestamps', $metadata->hasTimestamps());
            $this->checkFeature('Sluggable', $metadata->isSluggable());
            $this->checkFeature('Soft Deletes', $metadata->hasSoftDeletes());
            $this->checkFeature('Permissions', $metadata->hasPermissions());
            $this->checkFeature('Row-Level Security', $metadata->hasRowLevelSecurity());
            $this->checkFeature('Authentication', $metadata->hasAuthentication());
            $this->checkFeature('Searchable Fields', !empty($metadata->getSearchableFields()));
            
            echo "\n";
            $this->success('Metadata is valid');
            
        } catch (\Exception $e) {
            $this->error('Validation failed: ' . $e->getMessage());
            exit(1);
        }
    }
    
    private function checkFeature(string $name, $value): void
    {
        $status = $value ? '✅' : '⚪';
        $display = is_bool($value) ? ($value ? 'Enabled' : 'Disabled') : $value;
        echo sprintf("  %s %-25s %s\n", $status, $name . ':', $display);
    }
}
