<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\Theme\Themes\MinimalTheme;

class MinimalThemeTest extends TestCase
{
    private MinimalTheme $theme;
    private string $themesDir;
    
    protected function setUp(): void
    {
        $this->themesDir = __DIR__ . '/../themes';
        $this->theme = new MinimalTheme($this->themesDir);
    }
    
    public function testGetName(): void
    {
        $this->assertEquals('Minimal', $this->theme->getName());
    }
    
    public function testGetDescription(): void
    {
        $description = $this->theme->getDescription();
        $this->assertIsString($description);
        $this->assertStringContainsString('simple', strtolower($description));
    }
    
    public function testGetConfig(): void
    {
        $config = $this->theme->getConfig();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('colors', $config);
        $this->assertArrayHasKey('fonts', $config);
        $this->assertArrayHasKey('layout', $config);
    }
    
    public function testGetTemplates(): void
    {
        $templates = $this->theme->getTemplates();
        $this->assertIsArray($templates);
        $this->assertContains('home', $templates);
        $this->assertContains('single', $templates);
        $this->assertContains('layout', $templates);
    }
    
    public function testGetAssets(): void
    {
        $assets = $this->theme->getAssets();
        $this->assertIsArray($assets);
        $this->assertArrayHasKey('css', $assets);
        $this->assertArrayHasKey('js', $assets);
        $this->assertContains('style.css', $assets['css']);
    }
    
    public function testRenderTemplate(): void
    {
        $html = $this->theme->render('home', [
            'posts' => [
                ['title' => 'Test Post', 'slug' => 'test-post', 'excerpt' => 'Test', 'published_at' => '2025-01-01']
            ]
        ]);
        
        $this->assertStringContainsString('Test Post', $html);
        $this->assertStringContainsString('test-post', $html);
    }
    
    public function testRenderNonExistentTemplate(): void
    {
        $html = $this->theme->render('nonexistent', []);
        $this->assertStringContainsString('Template Not Found', $html);
    }
    
    public function testColorScheme(): void
    {
        $config = $this->theme->getConfig();
        $this->assertEquals('#333333', $config['colors']['primary']);
        $this->assertEquals('#ffffff', $config['colors']['background']);
    }
}
