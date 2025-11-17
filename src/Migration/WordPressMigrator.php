<?php

namespace Morpheus\Migration;

use PDO;

class WordPressMigrator
{
    private PDO $pdo;
    private string $prefix;
    private WXRParser $parser;
    private ContentMapper $mapper;
    private ?MediaDownloader $downloader;
    private array $urlMap = [];
    private array $stats = [
        'categories' => 0,
        'tags' => 0,
        'posts' => 0,
        'media' => 0,
        'errors' => []
    ];
    
    public function __construct(PDO $pdo, string $prefix = '', ?string $uploadDir = null)
    {
        $this->pdo = $pdo;
        $this->prefix = $prefix;
        $this->parser = new WXRParser();
        $this->mapper = new ContentMapper();
        $this->downloader = $uploadDir ? new MediaDownloader($uploadDir) : null;
    }
    
    public function migrate(string $wxrFile, array $options = []): array
    {
        $downloadMedia = $options['download_media'] ?? true;
        
        try {
            $this->pdo->beginTransaction();
            
            // Parse WXR file
            $data = $this->parser->parse($wxrFile);
            
            // Import categories
            $categoryMap = $this->importCategories($data['categories']);
            $this->mapper->setCategoryMap($categoryMap);
            
            // Import tags
            $tagMap = $this->importTags($data['tags']);
            $this->mapper->setTagMap($tagMap);
            
            // Import posts
            $this->importPosts($data['posts'], $downloadMedia);
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'stats' => $this->stats,
                'url_map' => $this->urlMap
            ];
            
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'stats' => $this->stats
            ];
        }
    }
    
    public function importCategories(array $categories): array
    {
        $map = [];
        
        foreach ($categories as $wpCat) {
            $data = $this->mapper->mapCategory($wpCat);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->prefix}categories (name, slug, description)
                VALUES (:name, :slug, :description)
            ");
            
            $stmt->execute($data);
            $id = (int) $this->pdo->lastInsertId();
            
            $map[$wpCat['slug']] = $id;
            $this->stats['categories']++;
        }
        
        return $map;
    }
    
    public function importTags(array $tags): array
    {
        $map = [];
        
        foreach ($tags as $wpTag) {
            $data = $this->mapper->mapTag($wpTag);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->prefix}tags (name, slug)
                VALUES (:name, :slug)
            ");
            
            $stmt->execute($data);
            $id = (int) $this->pdo->lastInsertId();
            
            $map[$wpTag['slug']] = $id;
            $this->stats['tags']++;
        }
        
        return $map;
    }
    
    public function importPosts(array $posts, bool $downloadMedia = true): void
    {
        foreach ($posts as $wpPost) {
            try {
                $data = $this->mapper->mapPost($wpPost);
                
                // Download media if enabled
                if ($downloadMedia && $this->downloader) {
                    $imageUrls = $this->mapper->extractImageUrls($data['content']);
                    if (!empty($imageUrls)) {
                        $downloaded = $this->downloader->downloadBatch($imageUrls);
                        
                        // Build URL map for replacement
                        $urlMap = [];
                        foreach ($downloaded as $oldUrl => $filename) {
                            $urlMap[$oldUrl] = '/uploads/' . $filename;
                        }
                        
                        // Replace URLs in content
                        $data['content'] = $this->mapper->replaceImageUrls($data['content'], $urlMap);
                        
                        $this->stats['media'] += count($downloaded);
                    }
                }
                
                // Insert post
                $stmt = $this->pdo->prepare("
                    INSERT INTO {$this->prefix}posts 
                    (title, slug, content, excerpt, status, published_at, category_id, featured_image)
                    VALUES (:title, :slug, :content, :excerpt, :status, :published_at, :category_id, :featured_image)
                ");
                
                $stmt->execute([
                    'title' => $data['title'],
                    'slug' => $data['slug'],
                    'content' => $data['content'],
                    'excerpt' => $data['excerpt'],
                    'status' => $data['status'],
                    'published_at' => $data['published_at'],
                    'category_id' => $data['category_id'],
                    'featured_image' => $data['featured_image']
                ]);
                
                $postId = (int) $this->pdo->lastInsertId();
                
                // Import post tags
                $tagIds = $this->mapper->mapPostTags($wpPost);
                $this->importPostTags($postId, $tagIds);
                
                // Store URL mapping
                $newUrl = '/blog/' . $data['slug'];
                $this->urlMap[$data['wp_link']] = $newUrl;
                
                $this->stats['posts']++;
                
            } catch (\Exception $e) {
                $this->stats['errors'][] = [
                    'post' => $wpPost['title'],
                    'error' => $e->getMessage()
                ];
            }
        }
    }
    
    private function importPostTags(int $postId, array $tagIds): void
    {
        foreach ($tagIds as $tagId) {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->prefix}post_tags (post_id, tag_id)
                VALUES (:post_id, :tag_id)
            ");
            
            $stmt->execute([
                'post_id' => $postId,
                'tag_id' => $tagId
            ]);
        }
    }
    
    public function getUrlMap(): array
    {
        return $this->urlMap;
    }
    
    public function getStats(): array
    {
        return $this->stats;
    }
    
    public function generateRedirects(string $format = 'htaccess'): string
    {
        if ($format === 'htaccess') {
            return $this->generateHtaccessRedirects();
        }
        
        return $this->generateNginxRedirects();
    }
    
    private function generateHtaccessRedirects(): string
    {
        $rules = "# WordPress to DynamicCRUD Redirects\n";
        $rules .= "# Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $rules .= "RewriteEngine On\n\n";
        
        foreach ($this->urlMap as $oldUrl => $newUrl) {
            $oldPath = parse_url($oldUrl, PHP_URL_PATH);
            $rules .= "RewriteRule ^" . ltrim($oldPath, '/') . "$ {$newUrl} [R=301,L]\n";
        }
        
        return $rules;
    }
    
    private function generateNginxRedirects(): string
    {
        $rules = "# WordPress to DynamicCRUD Redirects\n";
        $rules .= "# Generated: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($this->urlMap as $oldUrl => $newUrl) {
            $oldPath = parse_url($oldUrl, PHP_URL_PATH);
            $rules .= "rewrite ^{$oldPath}$ {$newUrl} permanent;\n";
        }
        
        return $rules;
    }
}
