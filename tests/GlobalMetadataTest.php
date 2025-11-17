<?php

namespace Morpheus\Tests;

use PHPUnit\Framework\TestCase;
use Morpheus\GlobalMetadata;

class GlobalMetadataTest extends TestCase
{
    private \PDO $pdo;
    private GlobalMetadata $config;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $this->config = new GlobalMetadata($this->pdo);
        $this->config->clear();
    }

    protected function tearDown(): void
    {
        $this->config->clear();
    }

    public function testSetAndGet(): void
    {
        $this->config->set('app.name', 'Test App');
        $this->assertEquals('Test App', $this->config->get('app.name'));
    }

    public function testGetWithDefault(): void
    {
        $this->assertEquals('default', $this->config->get('nonexistent', 'default'));
    }

    public function testSetComplexValue(): void
    {
        $value = [
            'name' => 'My App',
            'version' => '1.0.0',
            'features' => ['auth', 'api']
        ];
        
        $this->config->set('application', $value);
        $this->assertEquals($value, $this->config->get('application'));
    }

    public function testHas(): void
    {
        $this->assertFalse($this->config->has('test.key'));
        
        $this->config->set('test.key', 'value');
        $this->assertTrue($this->config->has('test.key'));
    }

    public function testDelete(): void
    {
        $this->config->set('to.delete', 'value');
        $this->assertTrue($this->config->has('to.delete'));
        
        $this->assertTrue($this->config->delete('to.delete'));
        $this->assertFalse($this->config->has('to.delete'));
    }

    public function testDeleteNonExistent(): void
    {
        $this->assertFalse($this->config->delete('nonexistent'));
    }

    public function testAll(): void
    {
        $this->config->set('key1', 'value1');
        $this->config->set('key2', ['nested' => 'value2']);
        
        $all = $this->config->all();
        
        $this->assertCount(2, $all);
        $this->assertEquals('value1', $all['key1']);
        $this->assertEquals(['nested' => 'value2'], $all['key2']);
    }

    public function testClear(): void
    {
        $this->config->set('key1', 'value1');
        $this->config->set('key2', 'value2');
        
        $this->assertCount(2, $this->config->all());
        
        $this->config->clear();
        
        $this->assertCount(0, $this->config->all());
    }

    public function testCaching(): void
    {
        $this->config->set('cached.key', 'value');
        
        // First call hits database
        $value1 = $this->config->get('cached.key');
        
        // Second call should use cache
        $value2 = $this->config->get('cached.key');
        
        $this->assertEquals($value1, $value2);
    }

    public function testUpdateExisting(): void
    {
        $this->config->set('update.key', 'original');
        $this->assertEquals('original', $this->config->get('update.key'));
        
        $this->config->set('update.key', 'updated');
        $this->assertEquals('updated', $this->config->get('update.key'));
    }

    public function testUnicodeSupport(): void
    {
        $value = ['name' => 'Aplicación', 'emoji' => '🚀'];
        $this->config->set('unicode', $value);
        
        $retrieved = $this->config->get('unicode');
        $this->assertEquals('Aplicación', $retrieved['name']);
        $this->assertEquals('🚀', $retrieved['emoji']);
    }
}
