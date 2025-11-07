<?php

namespace DynamicCRUD\Theme;

use PDO;
use DynamicCRUD\GlobalMetadata;

class ThemeManager
{
    private PDO $pdo;
    private string $themesDir;
    private array $themes = [];
    private ?Theme $activeTheme = null;
    private GlobalMetadata $config;
    
    public function __construct(PDO $pdo, string $themesDir)
    {
        $this->pdo = $pdo;
        $this->themesDir = $themesDir;
        $this->config = new GlobalMetadata($pdo);
    }
    
    private function loadActiveTheme(): void
    {
        $activeThemeName = $this->config->get('theme.active');
        
        if ($activeThemeName && isset($this->themes[$activeThemeName])) {
            $this->activeTheme = $this->themes[$activeThemeName];
        }
    }
    
    public function register(string $name, Theme $theme): void
    {
        $this->themes[$name] = $theme;
    }
    
    public function getAvailable(): array
    {
        return array_map(function($theme) {
            return [
                'name' => $theme->getName(),
                'description' => $theme->getDescription(),
                'version' => $theme->getVersion(),
                'author' => $theme->getAuthor(),
                'screenshot' => $theme->getScreenshot()
            ];
        }, $this->themes);
    }
    
    public function getActive(): ?Theme
    {
        // Lazy load active theme
        if ($this->activeTheme === null && !empty($this->themes)) {
            $this->loadActiveTheme();
        }
        return $this->activeTheme;
    }
    
    public function activate(string $name): bool
    {
        if (!isset($this->themes[$name])) {
            return false;
        }
        
        try {
            $this->config->set('theme.active', $name);
            $this->activeTheme = $this->themes[$name];
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function deactivate(): bool
    {
        try {
            $this->config->delete('theme.active');
            $this->activeTheme = null;
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getThemeInfo(string $name): array
    {
        if (!isset($this->themes[$name])) {
            return [];
        }
        
        $theme = $this->themes[$name];
        return [
            'name' => $theme->getName(),
            'description' => $theme->getDescription(),
            'version' => $theme->getVersion(),
            'author' => $theme->getAuthor(),
            'screenshot' => $theme->getScreenshot(),
            'config' => $theme->getConfig(),
            'templates' => $theme->getTemplates(),
            'assets' => $theme->getAssets()
        ];
    }
    
    public function isInstalled(string $name): bool
    {
        return isset($this->themes[$name]);
    }
    
    public function render(string $template, array $data): string
    {
        if (!$this->activeTheme) {
            return $this->renderFallback($template, $data);
        }
        
        return $this->activeTheme->render($template, $data);
    }
    
    private function renderFallback(string $template, array $data): string
    {
        return sprintf(
            '<div style="padding:20px;background:#fff3cd;border:1px solid #ffc107;"><strong>No Active Theme</strong><p>Please activate a theme to render templates.</p></div>'
        );
    }
    
    public function getConfig(?string $key = null): mixed
    {
        if (!$this->activeTheme) {
            return null;
        }
        
        $config = $this->activeTheme->getConfig();
        
        if ($key === null) {
            return $config;
        }
        
        // Support dot notation
        $keys = explode('.', $key);
        $value = $config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return null;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    public function setConfig(string $key, mixed $value): bool
    {
        if (!$this->activeTheme) {
            return false;
        }
        
        try {
            $themeName = $this->activeTheme->getName();
            $configKey = "theme.config.{$themeName}.{$key}";
            $this->config->set($configKey, $value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
