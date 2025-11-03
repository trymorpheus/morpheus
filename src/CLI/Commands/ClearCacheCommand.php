<?php

namespace DynamicCRUD\CLI\Commands;

class ClearCacheCommand extends Command
{
    public function execute(array $args): void
    {
        $this->info('Clearing cache...');
        
        $cacheDir = getcwd() . '/cache';
        $cleared = 0;
        
        if (!is_dir($cacheDir)) {
            $this->warning('Cache directory not found');
            return;
        }
        
        // Clear template cache
        $templateCache = $cacheDir . '/templates';
        if (is_dir($templateCache)) {
            $cleared += $this->clearDirectory($templateCache);
        }
        
        // Clear schema cache files
        $files = glob($cacheDir . '/*.cache');
        foreach ($files as $file) {
            if (unlink($file)) {
                $cleared++;
            }
        }
        
        $this->success("Cache cleared: $cleared files deleted");
    }
    
    private function clearDirectory(string $dir): int
    {
        $count = 0;
        $files = glob($dir . '/*');
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }
}
