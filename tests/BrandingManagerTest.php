<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\GlobalMetadata;
use Morpheus\BrandingManager;
use PDO;

class BrandingManagerTest extends TestCase
{
    private PDO $pdo;
    private GlobalMetadata $config;
    private BrandingManager $branding;
    
    protected function setUp(): void
    {
        $this->pdo = new PDO('mysql:host=127.0.0.1;dbname=test', 'root', 'rootpassword');
        $this->config = new GlobalMetadata($this->pdo);
        $this->branding = new BrandingManager($this->config);
        
        // Clear branding config
        $this->config->delete('branding');
    }
    
    public function testRenderFavicon(): void
    {
        $this->config->set('branding.favicon', '/favicon.ico');
        $html = $this->branding->renderFavicon();
        
        $this->assertStringContainsString('<link rel="icon"', $html);
        $this->assertStringContainsString('/favicon.ico', $html);
    }
    
    public function testRenderCSSVariables(): void
    {
        $this->config->set('branding.colors', ['primary' => '#667eea']);
        $html = $this->branding->renderCSSVariables();
        
        $this->assertStringContainsString('--brand-primary: #667eea', $html);
    }
    
    public function testRenderLogo(): void
    {
        $this->config->set('branding.logo', '/logo.png');
        $this->config->set('branding.app_name', 'Test App');
        $html = $this->branding->renderLogo();
        
        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString('/logo.png', $html);
    }
    
    public function testRenderLogoWithoutImage(): void
    {
        $this->config->delete('branding.logo');
        $this->config->set('branding.app_name', 'Test App');
        $html = $this->branding->renderLogo();
        
        $this->assertStringContainsString('<h1', $html);
        $this->assertStringContainsString('Test App', $html);
    }
    
    public function testRenderNavigation(): void
    {
        $this->config->set('branding.navigation', [
            'position' => 'top',
            'items' => [
                ['label' => 'Home', 'url' => '/', 'icon' => '🏠']
            ]
        ]);
        
        $html = $this->branding->renderNavigation();
        
        $this->assertStringContainsString('<nav', $html);
        $this->assertStringContainsString('Home', $html);
    }
    
    public function testGetAppName(): void
    {
        $this->config->set('branding.app_name', 'My App');
        $this->assertEquals('My App', $this->branding->getAppName());
    }
    
    public function testGetColors(): void
    {
        $colors = ['primary' => '#667eea', 'secondary' => '#764ba2'];
        $this->config->set('branding.colors', $colors);
        $this->assertEquals($colors, $this->branding->getColors());
    }
    
    public function testRenderDarkMode(): void
    {
        $this->config->set('branding.dark_mode', true);
        $html = $this->branding->renderCSSVariables();
        
        $this->assertStringContainsString('@media (prefers-color-scheme: dark)', $html);
    }
    
    public function testRenderCustomCSS(): void
    {
        $this->config->set('branding.custom_css', '.custom { color: red; }');
        $html = $this->branding->renderCustomCSS();
        
        $this->assertStringContainsString('.custom { color: red; }', $html);
    }
}
