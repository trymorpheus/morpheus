<?php

namespace Morpheus\Migration;

class ContentMapper
{
    private array $categoryMap = [];
    private array $tagMap = [];
    
    public function setCategoryMap(array $map): void
    {
        $this->categoryMap = $map;
    }
    
    public function setTagMap(array $map): void
    {
        $this->tagMap = $map;
    }
    
    public function mapCategory(array $wpCategory): array
    {
        return [
            'name' => $wpCategory['name'],
            'slug' => $wpCategory['slug'],
            'description' => ''
        ];
    }
    
    public function mapTag(array $wpTag): array
    {
        return [
            'name' => $wpTag['name'],
            'slug' => $wpTag['slug']
        ];
    }
    
    public function mapPost(array $wpPost): array
    {
        $status = $this->convertStatus($wpPost['status']);
        $publishedAt = $status === 'published' ? $this->convertDate($wpPost['published_at']) : null;
        
        return [
            'title' => $wpPost['title'],
            'slug' => $wpPost['slug'],
            'content' => $this->convertContent($wpPost['content']),
            'excerpt' => $wpPost['excerpt'],
            'status' => $status,
            'published_at' => $publishedAt,
            'category_id' => $this->mapCategorySlug($wpPost['categories'][0] ?? null),
            'featured_image' => null,
            'wp_id' => $wpPost['id'],
            'wp_link' => $wpPost['link']
        ];
    }
    
    public function mapPostTags(array $wpPost): array
    {
        $tags = [];
        foreach ($wpPost['tags'] as $tagSlug) {
            if (isset($this->tagMap[$tagSlug])) {
                $tags[] = $this->tagMap[$tagSlug];
            }
        }
        return $tags;
    }
    
    private function convertStatus(string $wpStatus): string
    {
        return match($wpStatus) {
            'publish' => 'published',
            'draft' => 'draft',
            'pending' => 'draft',
            'private' => 'draft',
            default => 'draft'
        };
    }
    
    private function convertDate(string $wpDate): ?string
    {
        if (empty($wpDate) || $wpDate === '0000-00-00 00:00:00') {
            return null;
        }
        
        try {
            $date = new \DateTime($wpDate);
            return $date->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
    
    private function convertContent(string $html): string
    {
        // Remove WordPress-specific shortcodes
        $html = preg_replace('/\[caption[^\]]*\](.*?)\[\/caption\]/s', '$1', $html);
        $html = preg_replace('/\[gallery[^\]]*\]/s', '', $html);
        
        // Convert WordPress image classes
        $html = str_replace('class="aligncenter"', 'style="display:block;margin:0 auto;"', $html);
        $html = str_replace('class="alignleft"', 'style="float:left;margin:0 20px 20px 0;"', $html);
        $html = str_replace('class="alignright"', 'style="float:right;margin:0 0 20px 20px;"', $html);
        
        return $html;
    }
    
    private function mapCategorySlug(?string $slug): ?int
    {
        if ($slug === null) {
            return null;
        }
        
        return $this->categoryMap[$slug] ?? null;
    }
    
    public function extractImageUrls(string $content): array
    {
        $urls = [];
        
        // Extract img src attributes
        if (preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches)) {
            $urls = array_merge($urls, $matches[1]);
        }
        
        // Extract background images from style attributes
        if (preg_match_all('/background-image:\s*url\(["\']?([^"\']+)["\']?\)/i', $content, $matches)) {
            $urls = array_merge($urls, $matches[1]);
        }
        
        return array_unique($urls);
    }
    
    public function replaceImageUrls(string $content, array $urlMap): string
    {
        foreach ($urlMap as $oldUrl => $newUrl) {
            $content = str_replace($oldUrl, $newUrl, $content);
        }
        
        return $content;
    }
}
