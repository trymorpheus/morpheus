<?php

namespace Morpheus\Frontend;

/**
 * SEOManager
 * 
 * Handles SEO meta tags, Open Graph, Twitter Cards, Schema.org, sitemaps, and RSS
 */
class SEOManager
{
    private \PDO $pdo;
    private string $siteUrl;
    private string $siteName;
    private string $prefix;
    
    public function __construct(\PDO $pdo, string $siteUrl, string $siteName = 'My Site', string $prefix = '')
    {
        $this->pdo = $pdo;
        $this->siteUrl = rtrim($siteUrl, '/');
        $this->siteName = $siteName;
        $this->prefix = $prefix;
    }
    
    /**
     * Generate meta tags for a post
     */
    public function generateMetaTags(array $post): string
    {
        $title = htmlspecialchars($post['title'] ?? $this->siteName);
        $description = htmlspecialchars($post['excerpt'] ?? '');
        $url = $this->siteUrl . '/blog/' . ($post['slug'] ?? '');
        $image = $post['featured_image'] ?? '';
        
        $html = "<!-- SEO Meta Tags -->\n";
        $html .= "<title>{$title} - {$this->siteName}</title>\n";
        $html .= "<meta name=\"description\" content=\"{$description}\">\n";
        $html .= "<link rel=\"canonical\" href=\"{$url}\">\n";
        
        // Open Graph
        $html .= "\n<!-- Open Graph -->\n";
        $html .= "<meta property=\"og:type\" content=\"article\">\n";
        $html .= "<meta property=\"og:title\" content=\"{$title}\">\n";
        $html .= "<meta property=\"og:description\" content=\"{$description}\">\n";
        $html .= "<meta property=\"og:url\" content=\"{$url}\">\n";
        $html .= "<meta property=\"og:site_name\" content=\"{$this->siteName}\">\n";
        
        if ($image) {
            $html .= "<meta property=\"og:image\" content=\"{$this->siteUrl}/{$image}\">\n";
        }
        
        // Twitter Card
        $html .= "\n<!-- Twitter Card -->\n";
        $html .= "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
        $html .= "<meta name=\"twitter:title\" content=\"{$title}\">\n";
        $html .= "<meta name=\"twitter:description\" content=\"{$description}\">\n";
        
        if ($image) {
            $html .= "<meta name=\"twitter:image\" content=\"{$this->siteUrl}/{$image}\">\n";
        }
        
        return $html;
    }
    
    /**
     * Generate Schema.org JSON-LD for a post
     */
    public function generateSchemaOrg(array $post): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $post['title'] ?? '',
            'description' => $post['excerpt'] ?? '',
            'url' => $this->siteUrl . '/blog/' . ($post['slug'] ?? ''),
            'datePublished' => $post['published_at'] ?? date('c'),
            'dateModified' => $post['updated_at'] ?? date('c'),
            'author' => [
                '@type' => 'Person',
                'name' => $post['author_name'] ?? 'Admin'
            ]
        ];
        
        if (!empty($post['featured_image'])) {
            $schema['image'] = $this->siteUrl . '/' . $post['featured_image'];
        }
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
    }
    
    /**
     * Generate XML sitemap
     */
    public function generateSitemap(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Homepage
        $xml .= $this->addSitemapUrl($this->siteUrl, date('c'), '1.0', 'daily');
        
        // Posts
        $posts = $this->getPublishedPosts();
        foreach ($posts as $post) {
            $url = $this->siteUrl . '/blog/' . $post['slug'];
            $lastmod = $post['updated_at'] ?? $post['published_at'];
            $xml .= $this->addSitemapUrl($url, $lastmod, '0.8', 'weekly');
        }
        
        // Categories
        $categories = $this->getCategories();
        foreach ($categories as $category) {
            $url = $this->siteUrl . '/blog/category/' . $category['slug'];
            $xml .= $this->addSitemapUrl($url, date('c'), '0.6', 'weekly');
        }
        
        // Tags
        $tags = $this->getTags();
        foreach ($tags as $tag) {
            $url = $this->siteUrl . '/blog/tag/' . $tag['slug'];
            $xml .= $this->addSitemapUrl($url, date('c'), '0.5', 'weekly');
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
    
    /**
     * Generate RSS feed
     */
    public function generateRSS(int $limit = 20): string
    {
        $posts = $this->getPublishedPosts($limit);
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '<channel>' . "\n";
        $xml .= '  <title>' . htmlspecialchars($this->siteName) . '</title>' . "\n";
        $xml .= '  <link>' . $this->siteUrl . '</link>' . "\n";
        $xml .= '  <description>Latest posts from ' . htmlspecialchars($this->siteName) . '</description>' . "\n";
        $xml .= '  <language>en</language>' . "\n";
        $xml .= '  <atom:link href="' . $this->siteUrl . '/feed.xml" rel="self" type="application/rss+xml" />' . "\n";
        
        foreach ($posts as $post) {
            $xml .= '  <item>' . "\n";
            $xml .= '    <title>' . htmlspecialchars($post['title']) . '</title>' . "\n";
            $xml .= '    <link>' . $this->siteUrl . '/blog/' . $post['slug'] . '</link>' . "\n";
            $xml .= '    <guid>' . $this->siteUrl . '/blog/' . $post['slug'] . '</guid>' . "\n";
            $xml .= '    <pubDate>' . date('r', strtotime($post['published_at'])) . '</pubDate>' . "\n";
            $xml .= '    <description><![CDATA[' . ($post['excerpt'] ?? '') . ']]></description>' . "\n";
            
            if (!empty($post['content'])) {
                $xml .= '    <content:encoded><![CDATA[' . $post['content'] . ']]></content:encoded>' . "\n";
            }
            
            $xml .= '  </item>' . "\n";
        }
        
        $xml .= '</channel>' . "\n";
        $xml .= '</rss>';
        
        return $xml;
    }
    
    /**
     * Add URL to sitemap
     */
    private function addSitemapUrl(string $url, string $lastmod, string $priority, string $changefreq): string
    {
        $xml = "  <url>\n";
        $xml .= "    <loc>{$url}</loc>\n";
        $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
        $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
        $xml .= "    <priority>{$priority}</priority>\n";
        $xml .= "  </url>\n";
        
        return $xml;
    }
    
    /**
     * Get published posts
     */
    private function getPublishedPosts(int $limit = 1000): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->prefix}posts 
            WHERE status = 'published' AND deleted_at IS NULL 
            ORDER BY published_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get categories
     */
    private function getCategories(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->prefix}categories ORDER BY name");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get tags
     */
    private function getTags(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM {$this->prefix}tags ORDER BY name");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
