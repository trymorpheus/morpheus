<?php

namespace Morpheus\CLI\Commands;

use Morpheus\Metadata\TableMetadata;

class ListTablesCommand extends Command
{
    public function execute(array $args): void
    {
        $pdo = $this->getPDO();
        
        $this->info('Listing tables...');
        
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        
        if ($driver === 'mysql') {
            $sql = "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()";
        } else {
            $sql = "SELECT tablename as TABLE_NAME FROM pg_tables WHERE schemaname = 'public'";
        }
        
        $stmt = $pdo->query($sql);
        $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            $this->warning('No tables found');
            return;
        }
        
        echo "\n" . str_repeat('=', 80) . "\n";
        echo sprintf("%-30s %-15s %-35s\n", 'Table', 'Has Metadata', 'Features');
        echo str_repeat('=', 80) . "\n";
        
        foreach ($tables as $table) {
            try {
                $metadata = new TableMetadata($pdo, $table);
                $features = $this->getFeatures($metadata);
                $hasMetadata = !empty($features) ? 'Yes' : 'No';
                
                echo sprintf(
                    "%-30s %-15s %-35s\n",
                    $table,
                    $hasMetadata,
                    implode(', ', $features)
                );
            } catch (\Exception $e) {
                echo sprintf("%-30s %-15s %-35s\n", $table, 'Error', $e->getMessage());
            }
        }
        
        echo str_repeat('=', 80) . "\n\n";
        $this->success(count($tables) . ' tables found');
    }
    
    private function getFeatures(TableMetadata $metadata): array
    {
        $features = [];
        
        if ($metadata->hasTimestamps()) $features[] = 'Timestamps';
        if ($metadata->isSluggable()) $features[] = 'Sluggable';
        if ($metadata->hasSoftDeletes()) $features[] = 'Soft Deletes';
        if ($metadata->hasPermissions()) $features[] = 'RBAC';
        if ($metadata->hasAuthentication()) $features[] = 'Auth';
        if (!empty($metadata->getSearchableFields())) $features[] = 'Search';
        
        return $features;
    }
}
