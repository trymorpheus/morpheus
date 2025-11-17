<?php

namespace Morpheus\CLI\Commands;

use Morpheus\GlobalMetadata;

class BrandingSetCommand
{
    public function execute(array $args): int
    {
        $config = $this->getConfig();
        
        if (count($args) < 2) {
            echo "Usage: branding:set <key> <value>\n";
            echo "Examples:\n";
            echo "  branding:set app_name \"My App\"\n";
            echo "  branding:set logo \"/path/to/logo.png\"\n";
            echo "  branding:set primary_color \"#667eea\"\n";
            return 1;
        }
        
        $key = $args[0];
        $value = $args[1];
        
        // Map simple keys to full paths
        $keyMap = [
            'app_name' => 'branding.app_name',
            'logo' => 'branding.logo',
            'favicon' => 'branding.favicon',
            'primary_color' => 'branding.colors.primary',
            'secondary_color' => 'branding.colors.secondary',
            'background_color' => 'branding.colors.background',
            'text_color' => 'branding.colors.text',
            'font_family' => 'branding.fonts.family',
            'font_size' => 'branding.fonts.size',
            'max_width' => 'branding.layout.max_width',
            'padding' => 'branding.layout.padding',
            'border_radius' => 'branding.layout.border_radius',
            'custom_css' => 'branding.custom_css',
            'dark_mode' => 'branding.dark_mode'
        ];
        
        $fullKey = $keyMap[$key] ?? "branding.$key";
        
        // Handle nested keys
        $parts = explode('.', $fullKey);
        if (count($parts) > 2) {
            $base = $parts[0] . '.' . $parts[1];
            $current = $config->get($base, []);
            $current[$parts[2]] = $value;
            $config->set($base, $current);
        } else {
            $config->set($fullKey, $value);
        }
        
        echo "✓ Branding configuration updated: $key = $value\n";
        return 0;
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
