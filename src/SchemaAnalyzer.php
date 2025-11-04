<?php

namespace DynamicCRUD;

use PDO;
use DynamicCRUD\Cache\CacheStrategy;
use DynamicCRUD\Database\DatabaseAdapter;
use DynamicCRUD\Database\MySQLAdapter;
use DynamicCRUD\Database\PostgreSQLAdapter;

class SchemaAnalyzer
{
    private PDO $pdo;
    private DatabaseAdapter $adapter;
    private ?CacheStrategy $cache;
    private int $cacheTtl;

    public function __construct(PDO $pdo, ?CacheStrategy $cache = null, int $cacheTtl = 3600, ?DatabaseAdapter $adapter = null)
    {
        $this->pdo = $pdo;
        $this->cache = $cache;
        $this->cacheTtl = $cacheTtl;
        
        if ($adapter === null) {
            $this->adapter = $this->detectAdapter($pdo);
        } else {
            $this->adapter = $adapter;
        }
    }
    
    private function detectAdapter(PDO $pdo): DatabaseAdapter
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        return match($driver) {
            'mysql' => new MySQLAdapter($pdo),
            'pgsql' => new PostgreSQLAdapter($pdo),
            default => throw new \Exception("Unsupported database driver: {$driver}")
        };
    }

    public function getTableSchema(string $table): array
    {
        $cacheKey = $this->getCacheKey($table);
        
        $cached = $this->getCachedSchema($cacheKey);
        if ($cached !== null) {
            return $cached;
        }
        
        $schema = $this->adapter->getTableSchema($table);
        $this->cacheSchema($cacheKey, $schema);
        
        return $schema;
    }

    private function getCacheKey(string $table): string
    {
        return "schema_{$table}";
    }

    private function getCachedSchema(string $cacheKey): ?array
    {
        return $this->cache?->get($cacheKey);
    }

    private function cacheSchema(string $cacheKey, array $schema): void
    {
        $this->cache?->set($cacheKey, $schema, $this->cacheTtl);
    }

    public function invalidateCache(string $table): bool
    {
        if (!$this->cache) {
            return false;
        }
        
        return $this->cache->invalidate($this->getCacheKey($table));
    }
}
