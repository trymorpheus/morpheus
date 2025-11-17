<?php

namespace Morpheus\Cache;

class FileCacheStrategy implements CacheStrategy
{
    private string $cacheDir;

    public function __construct(?string $cacheDir = null)
    {
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir() . '/dynamiccrud_cache';
        
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get(string $key): ?array
    {
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) {
            return null;
        }
        
        $data = json_decode(file_get_contents($file), true);
        
        if ($data['expires_at'] < time()) {
            unlink($file);
            return null;
        }
        
        return $data['value'];
    }

    public function set(string $key, array $value, int $ttl = 3600): bool
    {
        $file = $this->getCacheFile($key);
        
        $data = [
            'value' => $value,
            'expires_at' => time() + $ttl
        ];
        
        return file_put_contents($file, json_encode($data)) !== false;
    }

    public function invalidate(string $key): bool
    {
        $file = $this->getCacheFile($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }

    public function clear(): bool
    {
        $files = glob($this->cacheDir . '/*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }

    private function getCacheFile(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.cache';
    }
}
