<?php

namespace Morpheus\Tests;

use Morpheus\Cache\FileCacheStrategy;
use PHPUnit\Framework\TestCase;

class FileCacheStrategyTest extends TestCase
{
    private string $cacheDir;
    private FileCacheStrategy $cache;

    protected function setUp(): void
    {
        $this->cacheDir = __DIR__ . '/temp_cache';
        $this->cache = new FileCacheStrategy($this->cacheDir);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->cacheDir)) {
            $files = glob($this->cacheDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            rmdir($this->cacheDir);
        }
    }

    public function testConstructorCreatesDirectory(): void
    {
        $this->assertDirectoryExists($this->cacheDir);
    }

    public function testSetAndGet(): void
    {
        $data = ['key' => 'value', 'number' => 42];
        
        $result = $this->cache->set('test_key', $data);
        $this->assertTrue($result);
        
        $retrieved = $this->cache->get('test_key');
        $this->assertEquals($data, $retrieved);
    }

    public function testGetNonExistent(): void
    {
        $result = $this->cache->get('nonexistent_key');
        $this->assertNull($result);
    }

    public function testSetWithTtl(): void
    {
        $data = ['test' => 'data'];
        
        $this->cache->set('ttl_key', $data, 1);
        $this->assertEquals($data, $this->cache->get('ttl_key'));
        
        sleep(2);
        
        $this->assertNull($this->cache->get('ttl_key'));
    }

    public function testInvalidate(): void
    {
        $this->cache->set('key_to_delete', ['data' => 'test']);
        $this->assertNotNull($this->cache->get('key_to_delete'));
        
        $result = $this->cache->invalidate('key_to_delete');
        $this->assertTrue($result);
        
        $this->assertNull($this->cache->get('key_to_delete'));
    }

    public function testInvalidateNonExistent(): void
    {
        $result = $this->cache->invalidate('nonexistent');
        $this->assertTrue($result);
    }

    public function testClear(): void
    {
        $this->cache->set('key1', ['data' => '1']);
        $this->cache->set('key2', ['data' => '2']);
        $this->cache->set('key3', ['data' => '3']);
        
        $result = $this->cache->clear();
        $this->assertTrue($result);
        
        $this->assertNull($this->cache->get('key1'));
        $this->assertNull($this->cache->get('key2'));
        $this->assertNull($this->cache->get('key3'));
    }

    public function testMultipleKeys(): void
    {
        $this->cache->set('key1', ['value' => 1]);
        $this->cache->set('key2', ['value' => 2]);
        
        $this->assertEquals(['value' => 1], $this->cache->get('key1'));
        $this->assertEquals(['value' => 2], $this->cache->get('key2'));
    }

    public function testOverwriteExisting(): void
    {
        $this->cache->set('key', ['old' => 'value']);
        $this->cache->set('key', ['new' => 'value']);
        
        $result = $this->cache->get('key');
        $this->assertEquals(['new' => 'value'], $result);
        $this->assertArrayNotHasKey('old', $result);
    }
}
