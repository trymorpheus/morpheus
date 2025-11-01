<?php

namespace DynamicCRUD\Cache;

interface CacheStrategy
{
    public function get(string $key): ?array;
    
    public function set(string $key, array $value, int $ttl = 3600): bool;
    
    public function invalidate(string $key): bool;
    
    public function clear(): bool;
}
