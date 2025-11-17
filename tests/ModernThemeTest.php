<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\Theme\Themes\ModernTheme;

class ModernThemeTest extends TestCase
{
    private ModernTheme $theme;
    private string $themesDir;
    
    protected function setUp(): void
    {
        $this->themesDir = __DIR__ . '/../themes';
        $this->theme = new ModernTheme($this->themesDir);
    }
    
    public function testGetName(): void
    {
        $this->assertEquals('Modern', $this->theme->getName());
    }
    
    public function testGetDescription(): void
    {
        $description = $this->theme->getDescription();
        $this->assertIsString($description);
        $this->assertStringContainsString('Modern', $description);
    }
    
    public function testGetConfig(): void
    {
        $config = $this->theme->getConfig();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('colors', $config);
        $this->assertArrayHasKey('features', $config);
    }
    
    public function testGetTemplates(): void
    {
        $templates = $this->theme->getTemplates();
        $this->assertIsArray($templates);
        $this->assertContains('home', $templates);
        $this->assertContains('single', $templates);
    }
    
    public function testGetAssets(): void
    {
        $assets = $this->theme->getAssets();
        $this->assertIsArray($assets);
        $this->assertContains('style.css', $assets['css']);
    }
    
    public function testRenderTemplate(): void
    {
        $html = $this->theme->render('home', [
            'posts' => [
                ['title' => 'Modern Post', 'slug' => 'modern-post', 'excerpt' => 'Test', 'published_at' => '2025-01-01']
            ]
        ]);
        
        $this->assertStringContainsString('Modern Post', $html);
    }
    
    public function testDarkModeFeature(): void
    {
        $config = $this->theme->getConfig();
        $this->assertTrue($config['features']['dark_mode']);
    }
    
    public function testAnimationsFeature(): void
    {
        $config = $this->theme->getConfig();
        $this->assertTrue($config['features']['animations']);
    }
}
