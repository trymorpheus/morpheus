<?php

namespace Morpheus\CLI\Commands;

use Morpheus\GlobalMetadata;

class BrandingShowCommand
{
    public function execute(array $args): int
    {
        $config = $this->getConfig();
        $branding = $config->get('branding', []);
        
        if (empty($branding)) {
            echo "No branding configuration found.\n";
            return 0;
        }
        
        echo "Current Branding Configuration:\n\n";
        
        $this->printSection('Application', [
            'App Name' => $branding['app_name'] ?? 'Not set',
            'Logo' => $branding['logo'] ?? 'Not set',
            'Favicon' => $branding['favicon'] ?? 'Not set'
        ]);
        
        if (isset($branding['colors'])) {
            $this->printSection('Colors', $branding['colors']);
        }
        
        if (isset($branding['fonts'])) {
            $this->printSection('Fonts', $branding['fonts']);
        }
        
        if (isset($branding['layout'])) {
            $this->printSection('Layout', $branding['layout']);
        }
        
        if (isset($branding['dark_mode'])) {
            echo "Dark Mode: " . ($branding['dark_mode'] ? 'Enabled' : 'Disabled') . "\n\n";
        }
        
        if (isset($branding['custom_css'])) {
            echo "Custom CSS: Configured\n\n";
        }
        
        return 0;
    }
    
    private function printSection(string $title, array $data): void
    {
        echo "$title:\n";
        foreach ($data as $key => $value) {
            echo sprintf("  %-20s %s\n", ucfirst(str_replace('_', ' ', $key)) . ':', $value);
        }
        echo "\n";
    }
    
    private function getConfig(): GlobalMetadata
    {
        $configFile = __DIR__ . '/../../../config.php';
        if (!file_exists($configFile)) {
            throw new \Exception('config.php not found. Run: php bin/morpheus init');
        }
        
        $config = require $configFile;
        $pdo = new \PDO($config['dsn'], $config['username'], $config['password'], $config['options'] ?? []);
        return new GlobalMetadata($pdo);
    }
}
