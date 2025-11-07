<?php

namespace DynamicCRUD\Migration;

class MediaDownloader
{
    private string $uploadDir;
    private array $downloaded = [];
    
    public function __construct(string $uploadDir)
    {
        $this->uploadDir = rtrim($uploadDir, '/\\');
        $this->ensureUploadDirectory();
    }
    
    public function download(string $url): ?string
    {
        if (isset($this->downloaded[$url])) {
            return $this->downloaded[$url];
        }
        
        if (!$this->isValidUrl($url)) {
            return null;
        }
        
        $filename = $this->generateFilename($url);
        $filepath = $this->uploadDir . '/' . $filename;
        
        if (file_exists($filepath)) {
            $this->downloaded[$url] = $filename;
            return $filename;
        }
        
        $content = $this->fetchUrl($url);
        if ($content === false) {
            return null;
        }
        
        if (file_put_contents($filepath, $content) === false) {
            return null;
        }
        
        $this->downloaded[$url] = $filename;
        return $filename;
    }
    
    public function downloadBatch(array $urls): array
    {
        $results = [];
        foreach ($urls as $url) {
            $filename = $this->download($url);
            if ($filename !== null) {
                $results[$url] = $filename;
            }
        }
        return $results;
    }
    
    public function getDownloadedCount(): int
    {
        return count($this->downloaded);
    }
    
    public function getDownloadedMap(): array
    {
        return $this->downloaded;
    }
    
    private function ensureUploadDirectory(): void
    {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    private function generateFilename(string $url): string
    {
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';
        $basename = basename($path);
        
        if (empty($basename)) {
            $basename = 'image_' . md5($url);
        }
        
        $basename = $this->sanitizeFilename($basename);
        
        // Add unique suffix if file exists
        $name = pathinfo($basename, PATHINFO_FILENAME);
        $ext = pathinfo($basename, PATHINFO_EXTENSION);
        $counter = 1;
        
        while (file_exists($this->uploadDir . '/' . $basename)) {
            $basename = $name . '_' . $counter . ($ext ? '.' . $ext : '');
            $counter++;
        }
        
        return $basename;
    }
    
    private function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        $filename = trim($filename, '_');
        
        return $filename;
    }
    
    private function fetchUrl(string $url): string|false
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'DynamicCRUD WordPress Migrator/1.0'
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        
        return @file_get_contents($url, false, $context);
    }
}
