<?php

namespace Morpheus;

class BrandingManager
{
    private GlobalMetadata $config;
    
    public function __construct(GlobalMetadata $config)
    {
        $this->config = $config;
    }
    
    public function renderBranding(): string
    {
        $html = $this->renderFavicon();
        $html .= $this->renderCSSVariables();
        $html .= $this->renderCustomCSS();
        return $html;
    }
    
    public function renderFavicon(): string
    {
        $favicon = $this->config->get('branding.favicon');
        if (!$favicon) {
            return '';
        }
        
        return sprintf('<link rel="icon" type="image/x-icon" href="%s">' . "\n", htmlspecialchars($favicon));
    }
    
    public function renderCSSVariables(): string
    {
        $colors = $this->config->get('branding.colors', []);
        $fonts = $this->config->get('branding.fonts', []);
        $layout = $this->config->get('branding.layout', []);
        $darkMode = $this->config->get('branding.dark_mode', false);
        
        if (empty($colors) && empty($fonts) && empty($layout)) {
            return '';
        }
        
        $css = '<style>' . "\n:root {\n";
        
        // Colors
        foreach ($colors as $key => $value) {
            $css .= sprintf("  --brand-%s: %s;\n", $key, $value);
        }
        
        // Fonts
        if (isset($fonts['family'])) {
            $css .= sprintf("  --brand-font-family: %s;\n", $fonts['family']);
        }
        if (isset($fonts['size'])) {
            $css .= sprintf("  --brand-font-size: %s;\n", $fonts['size']);
        }
        
        // Layout
        if (isset($layout['max_width'])) {
            $css .= sprintf("  --brand-max-width: %s;\n", $layout['max_width']);
        }
        if (isset($layout['padding'])) {
            $css .= sprintf("  --brand-padding: %s;\n", $layout['padding']);
        }
        if (isset($layout['border_radius'])) {
            $css .= sprintf("  --brand-border-radius: %s;\n", $layout['border_radius']);
        }
        
        $css .= "}\n";
        
        // Dark mode
        if ($darkMode) {
            $css .= $this->renderDarkMode();
        }
        
        // Apply variables
        $css .= "body { font-family: var(--brand-font-family, sans-serif); font-size: var(--brand-font-size, 16px); }\n";
        $css .= ".container { max-width: var(--brand-max-width, 1200px); padding: var(--brand-padding, 20px); }\n";
        
        $css .= '</style>' . "\n";
        
        return $css;
    }
    
    public function renderDarkMode(): string
    {
        $darkColors = $this->config->get('branding.dark_colors', [
            'background' => '#1a202c',
            'text' => '#e2e8f0',
            'primary' => '#667eea',
            'secondary' => '#718096'
        ]);
        
        $css = "@media (prefers-color-scheme: dark) {\n  :root {\n";
        
        foreach ($darkColors as $key => $value) {
            $css .= sprintf("    --brand-%s: %s;\n", $key, $value);
        }
        
        $css .= "  }\n  body { background: var(--brand-background); color: var(--brand-text); }\n}\n";
        
        return $css;
    }
    
    public function renderCustomCSS(): string
    {
        $customCSS = $this->config->get('branding.custom_css');
        if (!$customCSS) {
            return '';
        }
        
        return '<style>' . "\n" . $customCSS . "\n" . '</style>' . "\n";
    }
    
    public function renderLogo(): string
    {
        $logo = $this->config->get('branding.logo');
        $appName = $this->config->get('branding.app_name', 'DynamicCRUD');
        
        if ($logo) {
            return sprintf('<img src="%s" alt="%s" style="height: 40px;">', 
                htmlspecialchars($logo), 
                htmlspecialchars($appName)
            );
        }
        
        return sprintf('<h1 style="margin: 0;">%s</h1>', htmlspecialchars($appName));
    }
    
    public function renderNavigation(): string
    {
        $nav = $this->config->get('branding.navigation', []);
        if (empty($nav)) {
            return '';
        }
        
        $position = $nav['position'] ?? 'top';
        $items = $nav['items'] ?? [];
        
        if (empty($items)) {
            return '';
        }
        
        $html = sprintf('<nav class="brand-nav brand-nav-%s">' . "\n", $position);
        $html .= '  <ul>' . "\n";
        
        foreach ($items as $item) {
            $label = $item['label'] ?? '';
            $url = $item['url'] ?? '#';
            $icon = $item['icon'] ?? '';
            
            $html .= sprintf('    <li><a href="%s">%s %s</a></li>' . "\n",
                htmlspecialchars($url),
                $icon,
                htmlspecialchars($label)
            );
        }
        
        $html .= '  </ul>' . "\n";
        $html .= '</nav>' . "\n";
        
        return $html;
    }
    
    public function getAppName(): string
    {
        return $this->config->get('branding.app_name', 'DynamicCRUD');
    }
    
    public function getColors(): array
    {
        return $this->config->get('branding.colors', []);
    }
    
    public function getFonts(): array
    {
        return $this->config->get('branding.fonts', []);
    }
    
    public function getLayout(): array
    {
        return $this->config->get('branding.layout', []);
    }
}
