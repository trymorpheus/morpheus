<?php

namespace Morpheus\Theme;

abstract class AbstractTheme implements Theme
{
    protected string $themesDir;
    protected array $config = [];
    
    public function __construct(string $themesDir)
    {
        $this->themesDir = $themesDir;
        $this->loadConfig();
    }
    
    protected function loadConfig(): void
    {
        $configFile = $this->getThemeDir() . '/config.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        }
    }
    
    protected function getThemeDir(): string
    {
        return $this->themesDir . '/' . strtolower($this->getName());
    }
    
    public function getConfig(): array
    {
        return $this->config;
    }
    
    public function getTemplates(): array
    {
        $templatesDir = $this->getThemeDir() . '/templates';
        if (!is_dir($templatesDir)) {
            return [];
        }
        
        $templates = [];
        foreach (scandir($templatesDir) as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $templates[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }
        return $templates;
    }
    
    public function getAssets(): array
    {
        $assetsDir = $this->getThemeDir() . '/assets';
        if (!is_dir($assetsDir)) {
            return ['css' => [], 'js' => []];
        }
        
        $assets = ['css' => [], 'js' => []];
        foreach (scandir($assetsDir) as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext === 'css') {
                $assets['css'][] = $file;
            } elseif ($ext === 'js') {
                $assets['js'][] = $file;
            }
        }
        return $assets;
    }
    
    public function render(string $template, array $data): string
    {
        $templateFile = $this->getThemeDir() . '/templates/' . $template . '.php';
        
        if (!file_exists($templateFile)) {
            return $this->renderFallback($template, $data);
        }
        
        // Render template content
        extract($data);
        ob_start();
        include $templateFile;
        $content = ob_get_clean();
        
        // Wrap in layout if exists
        $layoutFile = $this->getThemeDir() . '/templates/layout.php';
        if (file_exists($layoutFile)) {
            $data['content'] = $content;
            $data['theme_styles'] = $this->getInlineStyles();
            extract($data);
            ob_start();
            include $layoutFile;
            return ob_get_clean();
        }
        
        return $content;
    }
    
    protected function getInlineStyles(): string
    {
        $styleFile = $this->getThemeDir() . '/assets/style.css';
        if (file_exists($styleFile)) {
            return file_get_contents($styleFile);
        }
        return '';
    }
    
    protected function renderFallback(string $template, array $data): string
    {
        return sprintf(
            '<div style="padding:20px;background:#f5f7fa;"><h1>Template Not Found</h1><p>Template "%s" not found in theme "%s"</p></div>',
            htmlspecialchars($template),
            htmlspecialchars($this->getName())
        );
    }
    
    public function getScreenshot(): string
    {
        return $this->config['screenshot'] ?? 'screenshot.png';
    }
    
    public function getVersion(): string
    {
        return $this->config['version'] ?? '1.0.0';
    }
    
    public function getAuthor(): string
    {
        return $this->config['author'] ?? 'Unknown';
    }
}
