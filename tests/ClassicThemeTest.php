<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\Theme\Themes\ClassicTheme;

class ClassicThemeTest extends TestCase
{
    private ClassicTheme $theme;
    private string $themesDir;
    
    protected function setUp(): void
    {
        $this->themesDir = __DIR__ . '/../themes';
        $this->theme = new ClassicTheme($this->themesDir);
    }
    
    public function testGetName(): void
    {
        $this->assertEquals('Classic', $this->theme->getName());
    }
    
    public function testGetDescription(): void
    {
        $description = $this->theme->getDescription();
        $this->assertIsString($description);
        $this->assertStringContainsString('Traditional', $description);
    }
    
    public function testGetConfig(): void
    {
        $config = $this->theme->getConfig();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('colors', $config);
        $this->assertArrayHasKey('layout', $config);
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
                ['title' => 'Classic Post', 'slug' => 'classic-post', 'excerpt' => 'Test', 'published_at' => '2025-01-01']
            ]
        ]);
        
        $this->assertStringContainsString('Classic Post', $html);
    }
    
    public function testSidebarLayout(): void
    {
        $config = $this->theme->getConfig();
        $this->assertTrue($config['layout']['sidebar']);
    }
    
    public function testSerifFonts(): void
    {
        $config = $this->theme->getConfig();
        $this->assertStringContainsString('Georgia', $config['fonts']['body']);
    }
}
