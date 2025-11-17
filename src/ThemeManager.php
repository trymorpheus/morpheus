<?php

namespace Morpheus;

class ThemeManager
{
    private GlobalMetadata $config;
    private array $defaultTheme = [
        'primary_color' => '#667eea',
        'secondary_color' => '#764ba2',
        'background_color' => '#ffffff',
        'text_color' => '#333333',
        'font_family' => 'system-ui, sans-serif',
        'border_radius' => '8px'
    ];

    public function __construct(GlobalMetadata $config)
    {
        $this->config = $config;
    }

    public function getTheme(): array
    {
        $theme = $this->config->get('theme', []);
        return array_merge($this->defaultTheme, $theme);
    }

    public function renderCSSVariables(): string
    {
        $theme = $this->getTheme();
        
        $css = '<style>:root {' . "\n";
        $css .= sprintf('  --primary-color: %s;', $theme['primary_color']) . "\n";
        $css .= sprintf('  --secondary-color: %s;', $theme['secondary_color']) . "\n";
        $css .= sprintf('  --background-color: %s;', $theme['background_color']) . "\n";
        $css .= sprintf('  --text-color: %s;', $theme['text_color']) . "\n";
        $css .= sprintf('  --font-family: %s;', $theme['font_family']) . "\n";
        $css .= sprintf('  --border-radius: %s;', $theme['border_radius']) . "\n";
        $css .= '}</style>' . "\n";
        
        return $css;
    }

    public function renderBranding(): string
    {
        $app = $this->config->get('application', []);
        
        if (empty($app)) {
            return '';
        }

        $html = '';
        
        if (isset($app['logo'])) {
            $html .= sprintf(
                '<div class="app-logo"><img src="%s" alt="%s"></div>',
                htmlspecialchars($app['logo']),
                htmlspecialchars($app['name'] ?? 'App')
            ) . "\n";
        }
        
        if (isset($app['name'])) {
            $html .= sprintf(
                '<div class="app-name">%s</div>',
                htmlspecialchars($app['name'])
            ) . "\n";
        }
        
        return $html ? '<div class="app-branding">' . $html . '</div>' : '';
    }

    public function applyThemeToStyles(string $css): string
    {
        $theme = $this->getTheme();
        
        // Replace hardcoded colors with CSS variables
        $css = str_replace('#667eea', 'var(--primary-color)', $css);
        $css = str_replace('#764ba2', 'var(--secondary-color)', $css);
        $css = str_replace('#5568d3', 'var(--primary-color)', $css);
        $css = str_replace('system-ui, sans-serif', 'var(--font-family)', $css);
        
        return $css;
    }
}
