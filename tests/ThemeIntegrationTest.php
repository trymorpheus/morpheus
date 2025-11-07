<?php

namespace DynamicCRUD\Tests;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\Theme\ThemeManager;
use DynamicCRUD\Theme\Themes\MinimalTheme;
use DynamicCRUD\Theme\Themes\ModernTheme;
use DynamicCRUD\Frontend\FrontendRenderer;
use DynamicCRUD\ContentTypes\ContentTypeManager;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ThemeIntegrationTest extends TestCase
{
    private \PDO $pdo;
    private string $themesDir;
    
    protected function setUp(): void
    {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->themesDir = __DIR__ . '/../themes';
        $this->cleanup();
        $this->setupBlog();
    }
    
    protected function tearDown(): void
    {
        $this->cleanup();
    }
    
    private function cleanup(): void
    {
        try {
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $this->pdo->exec("DELETE FROM _dynamiccrud_config WHERE config_key LIKE 'theme.%'");
            $this->pdo->exec("DROP TABLE IF EXISTS comments");
            $this->pdo->exec("DROP TABLE IF EXISTS post_tags");
            $this->pdo->exec("DROP TABLE IF EXISTS tags");
            $this->pdo->exec("DROP TABLE IF EXISTS posts");
            $this->pdo->exec("DROP TABLE IF EXISTS categories");
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        } catch (\Exception $e) {
            // Ignore
        }
    }
    
    private function setupBlog(): void
    {
        $manager = new ContentTypeManager($this->pdo);
        $manager->install('blog');
        
        // Add test data
        $this->pdo->exec("INSERT INTO categories (id, name, slug) VALUES (1, 'Tech', 'tech')");
        $this->pdo->exec("INSERT INTO posts (title, slug, content, excerpt, status, published_at) VALUES ('Test Post', 'test-post', '<p>Content</p>', 'Excerpt', 'published', NOW())");
    }
    
    public function testThemeManagerWithFrontendRenderer(): void
    {
        $themeManager = new ThemeManager($this->pdo, $this->themesDir);
        $themeManager->register('minimal', new MinimalTheme($this->themesDir));
        $themeManager->activate('minimal');
        
        $renderer = new FrontendRenderer($this->pdo, 'blog', null, null, '', $themeManager);
        $html = $renderer->renderHome();
        
        $this->assertStringContainsString('Test Post', $html);
    }
    
    public function testThemeSwitching(): void
    {
        $themeManager = new ThemeManager($this->pdo, $this->themesDir);
        $themeManager->register('minimal', new MinimalTheme($this->themesDir));
        $themeManager->register('modern', new ModernTheme($this->themesDir));
        
        // Activate minimal
        $themeManager->activate('minimal');
        $this->assertEquals('Minimal', $themeManager->getActive()->getName());
        
        // Switch to modern
        $themeManager->activate('modern');
        $this->assertEquals('Modern', $themeManager->getActive()->getName());
    }
    
    public function testThemeRendersCorrectTemplate(): void
    {
        $themeManager = new ThemeManager($this->pdo, $this->themesDir);
        $themeManager->register('minimal', new MinimalTheme($this->themesDir));
        $themeManager->activate('minimal');
        
        $renderer = new FrontendRenderer($this->pdo, 'blog', null, null, '', $themeManager);
        $html = $renderer->renderSingle('test-post');
        
        $this->assertStringContainsString('Test Post', $html);
        $this->assertStringContainsString('<p>Content</p>', $html);
    }
    
    public function testThemeConfigPersistence(): void
    {
        $themeManager = new ThemeManager($this->pdo, $this->themesDir);
        $themeManager->register('minimal', new MinimalTheme($this->themesDir));
        $themeManager->activate('minimal');
        
        // Set config
        $result = $themeManager->setConfig('custom.value', 'test');
        $this->assertTrue($result);
        
        // Verify it's saved in GlobalMetadata
        $stmt = $this->pdo->prepare("SELECT config_value FROM _dynamiccrud_config WHERE config_key = :key");
        $stmt->execute(['key' => 'theme.config.minimal.custom.value']);
        $value = $stmt->fetchColumn();
        
        // GlobalMetadata stores values as JSON
        $this->assertEquals('test', json_decode($value));
    }
    
    public function testMultipleThemesRegistered(): void
    {
        $themeManager = new ThemeManager($this->pdo, $this->themesDir);
        $themeManager->register('minimal', new MinimalTheme($this->themesDir));
        $themeManager->register('modern', new ModernTheme($this->themesDir));
        
        $available = $themeManager->getAvailable();
        
        $this->assertCount(2, $available);
        $this->assertArrayHasKey('minimal', $available);
        $this->assertArrayHasKey('modern', $available);
    }
    
    public function testThemeWithoutActivation(): void
    {
        $themeManager = new ThemeManager($this->pdo, $this->themesDir);
        $themeManager->register('minimal', new MinimalTheme($this->themesDir));
        
        $renderer = new FrontendRenderer($this->pdo, 'blog', null, null, '', $themeManager);
        $html = $renderer->renderHome();
        
        // Should show "No Active Theme" message
        $this->assertStringContainsString('No Active Theme', $html);
    }
    
    public function testThemeAssetsAvailable(): void
    {
        $themeManager = new ThemeManager($this->pdo, $this->themesDir);
        $themeManager->register('minimal', new MinimalTheme($this->themesDir));
        $themeManager->activate('minimal');
        
        $theme = $themeManager->getActive();
        $assets = $theme->getAssets();
        
        $this->assertArrayHasKey('css', $assets);
        $this->assertContains('style.css', $assets['css']);
    }
    
    public function testThemeTemplatesAvailable(): void
    {
        $themeManager = new ThemeManager($this->pdo, $this->themesDir);
        $themeManager->register('minimal', new MinimalTheme($this->themesDir));
        $themeManager->activate('minimal');
        
        $theme = $themeManager->getActive();
        $templates = $theme->getTemplates();
        
        $this->assertContains('home', $templates);
        $this->assertContains('single', $templates);
        $this->assertContains('layout', $templates);
    }
    
    public function testThemeColorConfiguration(): void
    {
        $themeManager = new ThemeManager($this->pdo, $this->themesDir);
        $themeManager->register('minimal', new MinimalTheme($this->themesDir));
        $themeManager->activate('minimal');
        
        $colors = $themeManager->getConfig('colors');
        
        $this->assertIsArray($colors);
        $this->assertArrayHasKey('primary', $colors);
        $this->assertArrayHasKey('background', $colors);
    }
    
    public function testThemeFontConfiguration(): void
    {
        $themeManager = new ThemeManager($this->pdo, $this->themesDir);
        $themeManager->register('minimal', new MinimalTheme($this->themesDir));
        $themeManager->activate('minimal');
        
        $fonts = $themeManager->getConfig('fonts');
        
        $this->assertIsArray($fonts);
        $this->assertArrayHasKey('heading', $fonts);
        $this->assertArrayHasKey('body', $fonts);
    }
}
