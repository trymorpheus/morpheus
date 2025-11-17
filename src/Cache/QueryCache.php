<?php

namespace Morpheus\Cache;

class QueryCache
{
    private array $cache = [];
    private int $hits = 0;
    private int $misses = 0;
    
    public function get(string $key): mixed
    {
        if (isset($this->cache[$key])) {
            $this->hits++;
            return $this->cache[$key];
        }
        
        $this->misses++;
        return null;
    }
    
    public function set(string $key, mixed $value): void
    {
        $this->cache[$key] = $value;
    }
    
    public function has(string $key): bool
    {
        return isset($this->cache[$key]);
    }
    
    public function clear(): void
    {
        $this->cache = [];
        $this->hits = 0;
        $this->misses = 0;
    }
    
    public function getStats(): array
    {
        return [
            'hits' => $this->hits,
            'misses' => $this->misses,
            'size' => count($this->cache),
            'hit_rate' => $this->hits + $this->misses > 0 
                ? round($this->hits / ($this->hits + $this->misses) * 100, 2) 
                : 0
        ];
    }
    
    public function generateKey(string $sql, array $params = []): string
    {
        return md5($sql . serialize($params));
    }
}
