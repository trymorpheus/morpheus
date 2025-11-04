<?php

namespace DynamicCRUD\Tests;

use PHPUnit\Framework\TestCase;
use DynamicCRUD\Cache\QueryCache;

class QueryCacheTest extends TestCase
{
    private QueryCache $cache;
    
    protected function setUp(): void
    {
        $this->cache = new QueryCache();
    }
    
    public function testSetAndGet(): void
    {
        $this->cache->set('key1', 'value1');
        $this->assertEquals('value1', $this->cache->get('key1'));
    }
    
    public function testGetMiss(): void
    {
        $this->assertNull($this->cache->get('nonexistent'));
    }
    
    public function testHas(): void
    {
        $this->cache->set('key1', 'value1');
        $this->assertTrue($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
    }
    
    public function testClear(): void
    {
        $this->cache->set('key1', 'value1');
        $this->cache->clear();
        $this->assertNull($this->cache->get('key1'));
    }
    
    public function testStats(): void
    {
        $this->cache->set('key1', 'value1');
        $this->cache->get('key1'); // hit
        $this->cache->get('key2'); // miss
        
        $stats = $this->cache->getStats();
        $this->assertEquals(1, $stats['hits']);
        $this->assertEquals(1, $stats['misses']);
        $this->assertEquals(1, $stats['size']);
        $this->assertEquals(50.0, $stats['hit_rate']);
    }
    
    public function testGenerateKey(): void
    {
        $key1 = $this->cache->generateKey('SELECT * FROM users', ['id' => 1]);
        $key2 = $this->cache->generateKey('SELECT * FROM users', ['id' => 1]);
        $key3 = $this->cache->generateKey('SELECT * FROM users', ['id' => 2]);
        
        $this->assertEquals($key1, $key2);
        $this->assertNotEquals($key1, $key3);
    }
}
