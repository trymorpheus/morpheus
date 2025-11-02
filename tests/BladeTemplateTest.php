<?php

use PHPUnit\Framework\TestCase;
use DynamicCRUD\Template\BladeTemplate;

class BladeTemplateTest extends TestCase
{
    private BladeTemplate $engine;
    private string $templatePath;
    private string $cachePath;

    protected function setUp(): void
    {
        $this->templatePath = __DIR__ . '/fixtures/templates';
        $this->cachePath = __DIR__ . '/fixtures/cache';
        
        if (!is_dir($this->templatePath)) {
            mkdir($this->templatePath, 0755, true);
        }
        
        $this->engine = new BladeTemplate($this->templatePath, $this->cachePath);
    }

    protected function tearDown(): void
    {
        // Clean up cache
        if (is_dir($this->cachePath)) {
            array_map('unlink', glob($this->cachePath . '/*'));
            rmdir($this->cachePath);
        }
    }

    public function testRenderSimpleVariable()
    {
        $template = 'Hello, {{ $name }}!';
        $result = $this->engine->render($template, ['name' => 'World']);
        
        $this->assertEquals('Hello, World!', $result);
    }

    public function testRenderEscapesHtml()
    {
        $template = '{{ $html }}';
        $result = $this->engine->render($template, ['html' => '<script>alert("xss")</script>']);
        
        $this->assertStringContainsString('&lt;script&gt;', $result);
        $this->assertStringNotContainsString('<script>', $result);
    }

    public function testRenderRawHtml()
    {
        $template = '{!! $html !!}';
        $result = $this->engine->render($template, ['html' => '<strong>Bold</strong>']);
        
        $this->assertEquals('<strong>Bold</strong>', $result);
    }

    public function testRenderIfStatement()
    {
        $template = '@if ($show)Visible@endif';
        
        $result1 = $this->engine->render($template, ['show' => true]);
        $this->assertEquals('Visible', $result1);
        
        $result2 = $this->engine->render($template, ['show' => false]);
        $this->assertEquals('', $result2);
    }

    public function testRenderIfElseStatement()
    {
        $template = '@if ($age >= 18)Adult@else Minor@endif';
        
        $result1 = $this->engine->render($template, ['age' => 25]);
        $this->assertEquals('Adult', $result1);
        
        $result2 = $this->engine->render($template, ['age' => 15]);
        $this->assertEquals(' Minor', $result2);
    }

    public function testRenderElseIfStatement()
    {
        $template = '@if ($score >= 90)A@elseif ($score >= 80)B@else C@endif';
        
        $this->assertEquals('A', $this->engine->render($template, ['score' => 95]));
        $this->assertEquals('B', $this->engine->render($template, ['score' => 85]));
        $this->assertEquals(' C', $this->engine->render($template, ['score' => 70]));
    }

    public function testRenderForeachLoop()
    {
        $template = '@foreach ($items as $item){{ $item }},@endforeach';
        $result = $this->engine->render($template, ['items' => ['a', 'b', 'c']]);
        
        $this->assertEquals('a,b,c,', $result);
    }

    public function testRenderForLoop()
    {
        $template = '@for ($i = 1; $i <= 3; $i++){{ $i }}@endfor';
        $result = $this->engine->render($template);
        
        $this->assertEquals('123', $result);
    }

    public function testRenderWithMissingVariable()
    {
        $template = '{{ $missing }}';
        $result = $this->engine->render($template, []);
        
        $this->assertEquals('', $result);
    }

    public function testRenderMultipleVariables()
    {
        $template = '{{ $first }} {{ $last }}';
        $result = $this->engine->render($template, ['first' => 'John', 'last' => 'Doe']);
        
        $this->assertEquals('John Doe', $result);
    }

    public function testRenderNestedConditions()
    {
        $template = '@if ($a)@if ($b)Both@endif@endif';
        
        $result1 = $this->engine->render($template, ['a' => true, 'b' => true]);
        $this->assertEquals('Both', $result1);
        
        $result2 = $this->engine->render($template, ['a' => true, 'b' => false]);
        $this->assertEquals('', $result2);
    }

    public function testRenderFile()
    {
        $templateFile = $this->templatePath . '/test.blade.php';
        file_put_contents($templateFile, 'Hello, {{ $name }}!');
        
        $result = $this->engine->renderFile('test.blade.php', ['name' => 'File']);
        
        $this->assertEquals('Hello, File!', $result);
        
        unlink($templateFile);
    }

    public function testRenderFileThrowsExceptionWhenNotFound()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Template not found');
        
        $this->engine->renderFile('nonexistent.blade.php', []);
    }

    public function testExists()
    {
        $templateFile = $this->templatePath . '/exists.blade.php';
        file_put_contents($templateFile, 'Content');
        
        $this->assertTrue($this->engine->exists('exists.blade.php'));
        $this->assertFalse($this->engine->exists('notexists.blade.php'));
        
        unlink($templateFile);
    }

    public function testCacheDirectoryCreated()
    {
        $newCachePath = __DIR__ . '/fixtures/newcache';
        
        if (is_dir($newCachePath)) {
            rmdir($newCachePath);
        }
        
        $engine = new BladeTemplate($this->templatePath, $newCachePath);
        
        $this->assertTrue(is_dir($newCachePath));
        
        rmdir($newCachePath);
    }

    public function testRenderWithArrayAccess()
    {
        $template = '{{ $user["name"] }}';
        $result = $this->engine->render($template, ['user' => ['name' => 'Alice']]);
        
        $this->assertEquals('Alice', $result);
    }

    public function testRenderWithObjectProperty()
    {
        $template = '{{ $user->name }}';
        $user = new stdClass();
        $user->name = 'Bob';
        $result = $this->engine->render($template, ['user' => $user]);
        
        $this->assertEquals('Bob', $result);
    }
}
