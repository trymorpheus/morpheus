<?php

namespace DynamicCRUD\Tests;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\Theme\ThemeManager;
use DynamicCRUD\Theme\Theme;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ThemeManagerTest extends TestCase
{
    private \PDO $pdo;
    private ThemeManager $manager;
    private string $themesDir;
    
    protected function setUp(): void
    {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $this->cleanup();
        
        $this->themesDir = __DIR__ . '/fixtures/themes';
        $this->manager = new ThemeManager($this->pdo, $this->themesDir);
    }
    
    protected function tearDown(): void
    {
        $this->cleanup();
    }
    
    private function cleanup(): void
    {
        try {
            $this->pdo->exec("DELETE FROM _dynamiccrud_config WHERE config_key LIKE 'theme.%'");
        } catch (\Exception $e) {
            // Ignore
        }
    }
    
    public function testThemesTableCreated(): void
    {
        // GlobalMetadata table should exist
        $stmt = $this->pdo->query("SHOW TABLES LIKE '_dynamiccrud_config'");
        $this->assertEquals(1, $stmt->rowCount());
    }
    
    public function testRegisterTheme(): void
    {
        $theme = $this->createMockTheme('Test Theme');
        $this->manager->register('test', $theme);
        
        $available = $this->manager->getAvailable();
        $this->assertArrayHasKey('test', $available);
        $this->assertEquals('Test Theme', $available['test']['name']);
    }
    
    public function testActivateTheme(): void
    {
        $theme = $this->createMockTheme('Test Theme');
        $this->manager->register('test', $theme);
        
        $result = $this->manager->activate('test');
        $this->assertTrue($result);
        
        $active = $this->manager->getActive();
        $this->assertNotNull($active);
        $this->assertEquals('Test Theme', $active->getName());
    }
    
    public function testActivateNonExistentTheme(): void
    {
        $result = $this->manager->activate('nonexistent');
        $this->assertFalse($result);
    }
    
    public function testDeactivateTheme(): void
    {
        $theme = $this->createMockTheme('Test Theme');
        $this->manager->register('test', $theme);
        $this->manager->activate('test');
        
        $result = $this->manager->deactivate();
        $this->assertTrue($result);
        
        $active = $this->manager->getActive();
        $this->assertNull($active);
    }
    
    public function testOnlyOneThemeActive(): void
    {
        $theme1 = $this->createMockTheme('Theme 1');
        $theme2 = $this->createMockTheme('Theme 2');
        
        $this->manager->register('theme1', $theme1);
        $this->manager->register('theme2', $theme2);
        
        $this->manager->activate('theme1');
        $this->manager->activate('theme2');
        
        // Check GlobalMetadata has only one active theme
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM _dynamiccrud_config WHERE config_key = 'theme.active'");
        $count = $stmt->fetchColumn();
        $this->assertEquals(1, $count);
    }
    
    public function testGetThemeInfo(): void
    {
        $theme = $this->createMockTheme('Test Theme');
        $this->manager->register('test', $theme);
        
        $info = $this->manager->getThemeInfo('test');
        
        $this->assertArrayHasKey('name', $info);
        $this->assertArrayHasKey('description', $info);
        $this->assertArrayHasKey('version', $info);
        $this->assertArrayHasKey('author', $info);
        $this->assertEquals('Test Theme', $info['name']);
    }
    
    public function testIsInstalled(): void
    {
        $theme = $this->createMockTheme('Test Theme');
        
        $this->assertFalse($this->manager->isInstalled('test'));
        
        $this->manager->register('test', $theme);
        $this->assertTrue($this->manager->isInstalled('test'));
    }
    
    public function testRenderWithActiveTheme(): void
    {
        $theme = $this->createMockTheme('Test Theme');
        $this->manager->register('test', $theme);
        $this->manager->activate('test');
        
        $html = $this->manager->render('home', ['title' => 'Test']);
        $this->assertStringContainsString('Test Theme', $html);
    }
    
    public function testRenderWithoutActiveTheme(): void
    {
        $html = $this->manager->render('home', []);
        $this->assertStringContainsString('No Active Theme', $html);
    }
    
    public function testGetConfig(): void
    {
        $theme = $this->createMockTheme('Test Theme', [
            'colors' => ['primary' => '#667eea']
        ]);
        $this->manager->register('test', $theme);
        $this->manager->activate('test');
        
        $config = $this->manager->getConfig();
        $this->assertIsArray($config);
        $this->assertArrayHasKey('colors', $config);
    }
    
    public function testGetConfigWithDotNotation(): void
    {
        $theme = $this->createMockTheme('Test Theme', [
            'colors' => ['primary' => '#667eea']
        ]);
        $this->manager->register('test', $theme);
        $this->manager->activate('test');
        
        $color = $this->manager->getConfig('colors.primary');
        $this->assertEquals('#667eea', $color);
    }
    
    public function testSetConfig(): void
    {
        $theme = $this->createMockTheme('test');
        $this->manager->register('test', $theme);
        $this->manager->activate('test');
        
        $result = $this->manager->setConfig('custom.setting', 'value');
        $this->assertTrue($result);
        
        // Verify in GlobalMetadata
        $stmt = $this->pdo->prepare("SELECT config_value FROM _dynamiccrud_config WHERE config_key = :key");
        $stmt->execute(['key' => 'theme.config.test.custom.setting']);
        $value = $stmt->fetchColumn();
        
        // GlobalMetadata stores values as JSON
        $this->assertEquals('value', json_decode($value));
    }
    
    public function testSetConfigWithoutActiveTheme(): void
    {
        $result = $this->manager->setConfig('test', 'value');
        $this->assertFalse($result);
    }
    
    public function testGetAvailable(): void
    {
        $theme1 = $this->createMockTheme('Theme 1');
        $theme2 = $this->createMockTheme('Theme 2');
        
        $this->manager->register('theme1', $theme1);
        $this->manager->register('theme2', $theme2);
        
        $available = $this->manager->getAvailable();
        $this->assertCount(2, $available);
        $this->assertArrayHasKey('theme1', $available);
        $this->assertArrayHasKey('theme2', $available);
    }
    
    private function createMockTheme(string $name, array $config = []): Theme
    {
        $theme = $this->createMock(Theme::class);
        
        $theme->method('getName')->willReturn($name);
        $theme->method('getDescription')->willReturn('Test description');
        $theme->method('getVersion')->willReturn('1.0.0');
        $theme->method('getAuthor')->willReturn('Test Author');
        $theme->method('getScreenshot')->willReturn('screenshot.png');
        $theme->method('getConfig')->willReturn($config);
        $theme->method('getTemplates')->willReturn(['home', 'single']);
        $theme->method('getAssets')->willReturn(['css' => ['style.css'], 'js' => []]);
        $theme->method('render')->willReturn('<html>' . $name . '</html>');
        
        return $theme;
    }
}
