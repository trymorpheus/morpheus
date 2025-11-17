<?php

namespace Morpheus\Frontend;

use Morpheus\Template\TemplateEngine;
use Morpheus\Theme\ThemeManager;

/**
 * FrontendRenderer
 * 
 * Renders public-facing pages from database content
 */
class FrontendRenderer
{
    private \PDO $pdo;
    private ?TemplateEngine $engine;
    private string $contentType;
    private ?SEOManager $seo;
    private string $prefix;
    private ?ThemeManager $themeManager;
    
    public function __construct(\PDO $pdo, string $contentType = 'blog', ?TemplateEngine $engine = null, ?SEOManager $seo = null, string $prefix = '', ?ThemeManager $themeManager = null)
    {
        $this->pdo = $pdo;
        $this->contentType = $contentType;
        $this->engine = $engine;
        $this->seo = $seo;
        $this->prefix = $prefix;
        $this->themeManager = $themeManager;
    }
    
    /**
     * Render single post/page
     */
    public function renderSingle(string $slug): string
    {
        $post = $this->getPostBySlug($slug);
        
        if (!$post) {
            return $this->render404();
        }
        
        $data = [
            'post' => $post,
            'title' => $post['title'],
            'content' => $post['content'],
            'seo_meta' => $this->seo ? $this->seo->generateMetaTags($post) : '',
            'seo_schema' => $this->seo ? $this->seo->generateSchemaOrg($post) : ''
        ];
        
        return $this->renderTemplate('single', $data);
    }
    
    /**
     * Render archive (list of posts)
     */
    public function renderArchive(int $page = 1, int $perPage = 10): string
    {
        $offset = ($page - 1) * $perPage;
        $posts = $this->getPosts($perPage, $offset);
        $total = $this->getTotalPosts();
        $totalPages = ceil($total / $perPage);
        
        $data = [
            'posts' => $posts,
            'page' => $page,
            'totalPages' => $totalPages,
            'title' => 'Blog'
        ];
        
        return $this->renderTemplate('archive', $data);
    }
    
    /**
     * Render category archive
     */
    public function renderCategory(string $slug, int $page = 1, int $perPage = 10): string
    {
        $category = $this->getCategoryBySlug($slug);
        
        if (!$category) {
            return $this->render404();
        }
        
        $offset = ($page - 1) * $perPage;
        $posts = $this->getPostsByCategory($category['id'], $perPage, $offset);
        $total = $this->getTotalPostsByCategory($category['id']);
        $totalPages = ceil($total / $perPage);
        
        $data = [
            'posts' => $posts,
            'category' => $category,
            'page' => $page,
            'totalPages' => $totalPages,
            'title' => $category['name']
        ];
        
        return $this->renderTemplate('category', $data);
    }
    
    /**
     * Render tag archive
     */
    public function renderTag(string $slug, int $page = 1, int $perPage = 10): string
    {
        $tag = $this->getTagBySlug($slug);
        
        if (!$tag) {
            return $this->render404();
        }
        
        $offset = ($page - 1) * $perPage;
        $posts = $this->getPostsByTag($tag['id'], $perPage, $offset);
        $total = $this->getTotalPostsByTag($tag['id']);
        $totalPages = ceil($total / $perPage);
        
        $data = [
            'posts' => $posts,
            'tag' => $tag,
            'page' => $page,
            'totalPages' => $totalPages,
            'title' => $tag['name']
        ];
        
        return $this->renderTemplate('tag', $data);
    }
    
    /**
     * Render home page
     */
    public function renderHome(): string
    {
        $posts = $this->getPosts(5);
        
        $data = [
            'posts' => $posts,
            'title' => 'Home'
        ];
        
        return $this->renderTemplate('home', $data);
    }
    
    /**
     * Render search results
     */
    public function renderSearch(string $query, int $page = 1, int $perPage = 10): string
    {
        $offset = ($page - 1) * $perPage;
        $posts = $this->searchPosts($query, $perPage, $offset);
        $total = $this->getTotalSearchResults($query);
        $totalPages = ceil($total / $perPage);
        
        $data = [
            'posts' => $posts,
            'query' => $query,
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'title' => "Search: {$query}"
        ];
        
        return $this->renderTemplate('search', $data);
    }
    
    /**
     * Render 404 page
     */
    public function render404(): string
    {
        http_response_code(404);
        
        $data = [
            'title' => '404 Not Found',
            'message' => 'The page you are looking for does not exist.'
        ];
        
        return $this->renderTemplate('404', $data);
    }
    
    /**
     * Render template
     */
    private function renderTemplate(string $template, array $data): string
    {
        // Priority: ThemeManager > TemplateEngine > Simple HTML
        if ($this->themeManager) {
            return $this->themeManager->render($template, $data);
        }
        
        if ($this->engine) {
            return $this->engine->render($template, $data);
        }
        
        // Fallback: simple HTML
        return $this->renderSimpleHTML($template, $data);
    }
    
    /**
     * Simple HTML fallback (no template engine)
     */
    private function renderSimpleHTML(string $template, array $data): string
    {
        extract($data);
        
        $html = '<!DOCTYPE html><html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $html .= '<title>' . htmlspecialchars($title ?? 'Blog') . '</title>';
        $html .= isset($data['seo_meta']) ? $data['seo_meta'] : '';
        $html .= '<style>';
        $html .= 'body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;margin:0;background:#f5f7fa;}';
        $html .= '.container{max-width:1200px;margin:0 auto;padding:20px;}';
        $html .= 'header{background:white;box-shadow:0 2px 4px rgba(0,0,0,0.1);margin-bottom:30px;}';
        $html .= 'nav{display:flex;justify-content:space-between;align-items:center;padding:20px;max-width:1200px;margin:0 auto;}';
        $html .= 'nav h1{margin:0;color:#667eea;}';
        $html .= 'nav a{margin-left:20px;color:#667eea;text-decoration:none;}';
        $html .= 'nav a:hover{text-decoration:underline;}';
        $html .= '.search-form{margin-bottom:30px;}';
        $html .= '.search-form input{padding:10px;width:300px;border:1px solid #ddd;border-radius:4px;}';
        $html .= '.search-form button{padding:10px 20px;background:#667eea;color:white;border:none;border-radius:4px;cursor:pointer;}';
        $html .= 'article{background:white;padding:30px;margin-bottom:20px;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}';
        $html .= 'article h2{margin-top:0;}';
        $html .= 'article h2 a{color:#333;text-decoration:none;}';
        $html .= 'article h2 a:hover{color:#667eea;}';
        $html .= '.meta{color:#666;font-size:14px;margin-bottom:15px;}';
        $html .= '</style>';
        $html .= '</head><body>';
        
        // Header with navigation
        $html .= '<header><nav>';
        $html .= '<h1>My Blog</h1>';
        $html .= '<div>';
        $html .= '<a href="/examples/24-blog-cms/">Home</a>';
        $html .= '<a href="/examples/24-blog-cms/blog">Blog</a>';
        $html .= '<a href="/examples/24-blog-cms/admin.php">Admin</a>';
        $html .= '</div>';
        $html .= '</nav></header>';
        
        $html .= '<div class="container">';
        
        // Search form
        if ($template !== 'single') {
            $html .= '<form class="search-form" method="GET" action="/examples/24-blog-cms/search">';
            $html .= '<input type="text" name="q" placeholder="Search posts..." value="' . htmlspecialchars($_GET['q'] ?? '') . '">';
            $html .= '<button type="submit">Search</button>';
            $html .= '</form>';
        }
        
        if ($template === 'single' && isset($post)) {
            $html .= '<article>';
            $html .= '<h1>' . htmlspecialchars($post['title']) . '</h1>';
            $html .= '<div class="meta">Published: ' . htmlspecialchars($post['published_at'] ?? '') . '</div>';
            $html .= '<div>' . $post['content'] . '</div>';
            $html .= '<p><a href="/examples/24-blog-cms/blog">← Back to blog</a></p>';
            $html .= '</article>';
        } elseif (isset($posts)) {
            $html .= '<h1>' . htmlspecialchars($title) . '</h1>';
            if (empty($posts)) {
                $html .= '<p>No posts found.</p>';
            } else {
                foreach ($posts as $post) {
                    $html .= '<article>';
                    $html .= '<h2><a href="/examples/24-blog-cms/blog/' . htmlspecialchars($post['slug']) . '">' . htmlspecialchars($post['title']) . '</a></h2>';
                    $html .= '<div class="meta">Published: ' . htmlspecialchars($post['published_at'] ?? '') . '</div>';
                    $html .= '<p>' . htmlspecialchars($post['excerpt'] ?? substr(strip_tags($post['content'] ?? ''), 0, 200)) . '...</p>';
                    $html .= '<a href="/examples/24-blog-cms/blog/' . htmlspecialchars($post['slug']) . '">Read more →</a>';
                    $html .= '</article>';
                }
            }
        }
        
        $html .= '</div>';
        $html .= isset($data['seo_schema']) ? $data['seo_schema'] : '';
        $html .= '</body></html>';
        return $html;
    }
    
    // Database queries
    
    private function getPostBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->prefix}posts WHERE slug = :slug AND status = 'published' AND deleted_at IS NULL LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    private function getPosts(int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->prefix}posts WHERE status = 'published' AND deleted_at IS NULL ORDER BY published_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    private function getTotalPosts(): int
    {
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM {$this->prefix}posts WHERE status = 'published' AND deleted_at IS NULL");
        return (int) $stmt->fetchColumn();
    }
    
    private function getCategoryBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->prefix}categories WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    private function getPostsByCategory(int $categoryId, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->prefix}posts WHERE category_id = :category_id AND status = 'published' AND deleted_at IS NULL ORDER BY published_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':category_id', $categoryId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    private function getTotalPostsByCategory(int $categoryId): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->prefix}posts WHERE category_id = :category_id AND status = 'published' AND deleted_at IS NULL");
        $stmt->execute(['category_id' => $categoryId]);
        return (int) $stmt->fetchColumn();
    }
    
    private function getTagBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->prefix}tags WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    private function getPostsByTag(int $tagId, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.* FROM {$this->prefix}posts p
            INNER JOIN {$this->prefix}post_tags pt ON p.id = pt.post_id
            WHERE pt.tag_id = :tag_id AND p.status = 'published' AND p.deleted_at IS NULL
            ORDER BY p.published_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':tag_id', $tagId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    private function getTotalPostsByTag(int $tagId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM {$this->prefix}posts p
            INNER JOIN {$this->prefix}post_tags pt ON p.id = pt.post_id
            WHERE pt.tag_id = :tag_id AND p.status = 'published' AND p.deleted_at IS NULL
        ");
        $stmt->execute(['tag_id' => $tagId]);
        return (int) $stmt->fetchColumn();
    }
    
    private function searchPosts(string $query, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->prefix}posts 
            WHERE (title LIKE :query OR content LIKE :query) 
            AND status = 'published' AND deleted_at IS NULL
            ORDER BY published_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $searchQuery = '%' . $query . '%';
        $stmt->bindValue(':query', $searchQuery);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    private function getTotalSearchResults(string $query): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM {$this->prefix}posts 
            WHERE (title LIKE :query OR content LIKE :query) 
            AND status = 'published' AND deleted_at IS NULL
        ");
        $searchQuery = '%' . $query . '%';
        $stmt->execute(['query' => $searchQuery]);
        return (int) $stmt->fetchColumn();
    }
}
