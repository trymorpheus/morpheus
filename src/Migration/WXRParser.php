<?php

namespace DynamicCRUD\Migration;

class WXRParser
{
    private array $namespaces = [
        'wp' => 'http://wordpress.org/export/1.2/',
        'content' => 'http://purl.org/rss/1.0/modules/content/',
        'excerpt' => 'http://wordpress.org/export/1.2/excerpt/',
        'dc' => 'http://purl.org/dc/elements/1.1/'
    ];
    
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }
        
        $xml = simplexml_load_file($filePath);
        if ($xml === false) {
            throw new \Exception("Failed to parse XML file");
        }
        
        foreach ($this->namespaces as $prefix => $uri) {
            $xml->registerXPathNamespace($prefix, $uri);
        }
        
        return [
            'site' => $this->parseChannel($xml->channel),
            'categories' => $this->parseCategories($xml),
            'tags' => $this->parseTags($xml),
            'posts' => $this->parsePosts($xml),
            'authors' => $this->parseAuthors($xml)
        ];
    }
    
    private function parseChannel(\SimpleXMLElement $channel): array
    {
        return [
            'title' => (string) $channel->title,
            'link' => (string) $channel->link,
            'description' => (string) $channel->description,
            'language' => (string) $channel->language
        ];
    }
    
    private function parseCategories(\SimpleXMLElement $xml): array
    {
        $categories = [];
        $items = $xml->xpath('//wp:category');
        
        foreach ($items as $item) {
            $categories[] = [
                'id' => (int) $item->children('wp', true)->term_id,
                'slug' => (string) $item->children('wp', true)->category_nicename,
                'name' => (string) $item->children('wp', true)->cat_name,
                'parent' => (string) $item->children('wp', true)->category_parent
            ];
        }
        
        return $categories;
    }
    
    private function parseTags(\SimpleXMLElement $xml): array
    {
        $tags = [];
        $items = $xml->xpath('//wp:tag');
        
        foreach ($items as $item) {
            $tags[] = [
                'id' => (int) $item->children('wp', true)->term_id,
                'slug' => (string) $item->children('wp', true)->tag_slug,
                'name' => (string) $item->children('wp', true)->tag_name
            ];
        }
        
        return $tags;
    }
    
    private function parsePosts(\SimpleXMLElement $xml): array
    {
        $posts = [];
        $items = $xml->xpath('//channel/item');
        
        foreach ($items as $item) {
            $wp = $item->children('wp', true);
            $content = $item->children('content', true);
            $excerpt = $item->children('excerpt', true);
            
            $postType = (string) $wp->post_type;
            if (!in_array($postType, ['post', 'page'])) {
                continue;
            }
            
            $post = [
                'id' => (int) $wp->post_id,
                'title' => (string) $item->title,
                'slug' => (string) $wp->post_name,
                'content' => (string) $content->encoded,
                'excerpt' => (string) $excerpt->encoded,
                'status' => (string) $wp->status,
                'type' => $postType,
                'published_at' => (string) $wp->post_date,
                'author' => (string) $item->children('dc', true)->creator,
                'link' => (string) $item->link,
                'categories' => [],
                'tags' => [],
                'featured_image' => null
            ];
            
            // Parse categories and tags
            foreach ($item->category as $cat) {
                $domain = (string) $cat['domain'];
                $nicename = (string) $cat['nicename'];
                
                if ($domain === 'category') {
                    $post['categories'][] = $nicename;
                } elseif ($domain === 'post_tag') {
                    $post['tags'][] = $nicename;
                }
            }
            
            // Parse featured image
            foreach ($wp->postmeta as $meta) {
                if ((string) $meta->meta_key === '_thumbnail_id') {
                    $post['featured_image'] = (int) $meta->meta_value;
                    break;
                }
            }
            
            $posts[] = $post;
        }
        
        return $posts;
    }
    
    private function parseAuthors(\SimpleXMLElement $xml): array
    {
        $authors = [];
        $items = $xml->xpath('//wp:author');
        
        foreach ($items as $item) {
            $authors[] = [
                'id' => (int) $item->children('wp', true)->author_id,
                'login' => (string) $item->children('wp', true)->author_login,
                'email' => (string) $item->children('wp', true)->author_email,
                'display_name' => (string) $item->children('wp', true)->author_display_name
            ];
        }
        
        return $authors;
    }
}
