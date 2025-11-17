<?php

namespace Morpheus\ContentTypes;

/**
 * BlogContentType
 * 
 * WordPress-style blog with posts, categories, tags, and comments
 */
class BlogContentType implements ContentType
{
    private string $prefix;
    
    public function __construct(string $prefix = '')
    {
        $this->prefix = $prefix;
    }
    
    public function getName(): string
    {
        return 'blog';
    }
    
    public function getDescription(): string
    {
        return 'Complete blog system with posts, categories, tags, and comments';
    }
    
    public function getTables(): array
    {
        return [
            $this->prefix . 'categories' => $this->getCategoriesTableSQL(),
            $this->prefix . 'tags' => $this->getTagsTableSQL(),
            $this->prefix . 'posts' => $this->getPostsTableSQL(),
            $this->prefix . 'post_tags' => $this->getPostTagsTableSQL(),
            $this->prefix . 'comments' => $this->getCommentsTableSQL()
        ];
    }
    
    public function getMetadata(): array
    {
        return [
            $this->prefix . 'posts' => [
                'display_name' => 'Blog Posts',
                'icon' => '📝',
                'list_view' => [
                    'columns' => ['title', 'category_id', 'status', 'published_at'],
                    'searchable' => ['title', 'content'],
                    'filters' => ['status', 'category_id'],
                    'per_page' => 20
                ],
                'behaviors' => [
                    'timestamps' => true,
                    'sluggable' => ['source' => 'title', 'target' => 'slug'],
                    'soft_deletes' => true
                ]
            ],
            $this->prefix . 'categories' => [
                'display_name' => 'Categories',
                'icon' => '📁',
                'behaviors' => [
                    'sluggable' => ['source' => 'name', 'target' => 'slug']
                ]
            ],
            $this->prefix . 'tags' => [
                'display_name' => 'Tags',
                'icon' => '🏷️',
                'behaviors' => [
                    'sluggable' => ['source' => 'name', 'target' => 'slug']
                ]
            ],
            $this->prefix . 'comments' => [
                'display_name' => 'Comments',
                'icon' => '💬',
                'list_view' => [
                    'columns' => ['author_name', 'post_id', 'status', 'created_at'],
                    'filters' => ['status', 'post_id']
                ],
                'behaviors' => [
                    'timestamps' => true
                ]
            ]
        ];
    }
    
    public function install(\PDO $pdo): bool
    {
        try {
            foreach ($this->getTables() as $table => $sql) {
                $pdo->exec($sql);
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    
    public function uninstall(\PDO $pdo): bool
    {
        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $pdo->exec("DROP TABLE IF EXISTS {$this->prefix}comments");
            $pdo->exec("DROP TABLE IF EXISTS {$this->prefix}post_tags");
            $pdo->exec("DROP TABLE IF EXISTS {$this->prefix}tags");
            $pdo->exec("DROP TABLE IF EXISTS {$this->prefix}posts");
            $pdo->exec("DROP TABLE IF EXISTS {$this->prefix}categories");
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            return true;
        } catch (\Exception $e) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            throw $e;
        }
    }
    
    public function isInstalled(\PDO $pdo): bool
    {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$this->prefix}posts'");
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function getSampleData(): array
    {
        return [
            'categories' => [
                ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Tech news and tutorials'],
                ['name' => 'Lifestyle', 'slug' => 'lifestyle', 'description' => 'Life, travel, and culture']
            ],
            'tags' => [
                ['name' => 'PHP', 'slug' => 'php'],
                ['name' => 'JavaScript', 'slug' => 'javascript'],
                ['name' => 'Tutorial', 'slug' => 'tutorial']
            ],
            'posts' => [
                [
                    'title' => 'Welcome to DynamicCRUD',
                    'slug' => 'welcome-to-dynamiccrud',
                    'content' => 'This is your first blog post. Edit or delete it to get started!',
                    'excerpt' => 'Welcome to your new blog powered by DynamicCRUD',
                    'status' => 'published',
                    'category_id' => 1,
                    'published_at' => date('Y-m-d H:i:s')
                ]
            ]
        ];
    }
    
    private function getPostsTableSQL(): string
    {
        $metadata = json_encode($this->getMetadata()[$this->prefix . 'posts']);
        
        return "CREATE TABLE IF NOT EXISTS {$this->prefix}posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL COMMENT '{\"type\": \"text\", \"label\": \"Title\", \"required\": true}',
            slug VARCHAR(255) UNIQUE NOT NULL COMMENT '{\"hidden\": true}',
            content TEXT COMMENT '{\"type\": \"textarea\", \"label\": \"Content\", \"rows\": 15}',
            excerpt TEXT COMMENT '{\"type\": \"textarea\", \"label\": \"Excerpt\", \"rows\": 3}',
            featured_image VARCHAR(255) COMMENT '{\"type\": \"file\", \"label\": \"Featured Image\", \"accept\": \"image/*\"}',
            status ENUM('draft', 'published', 'archived') DEFAULT 'draft' COMMENT '{\"type\": \"select\", \"label\": \"Status\"}',
            category_id INT COMMENT '{\"type\": \"select\", \"label\": \"Category\"}',
            author_id INT COMMENT '{\"type\": \"select\", \"label\": \"Author\"}',
            published_at DATETIME COMMENT '{\"type\": \"datetime-local\", \"label\": \"Publish Date\"}',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            deleted_at TIMESTAMP NULL,
            FOREIGN KEY (category_id) REFERENCES {$this->prefix}categories(id) ON DELETE SET NULL,
            INDEX idx_status (status),
            INDEX idx_published (published_at)
        ) COMMENT = '{$metadata}'";
    }
    
    private function getCategoriesTableSQL(): string
    {
        $metadata = json_encode($this->getMetadata()[$this->prefix . 'categories']);
        
        return "CREATE TABLE IF NOT EXISTS {$this->prefix}categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL COMMENT '{\"type\": \"text\", \"label\": \"Name\", \"required\": true}',
            slug VARCHAR(100) UNIQUE NOT NULL COMMENT '{\"hidden\": true}',
            description TEXT COMMENT '{\"type\": \"textarea\", \"label\": \"Description\"}',
            parent_id INT NULL COMMENT '{\"type\": \"select\", \"label\": \"Parent Category\"}',
            FOREIGN KEY (parent_id) REFERENCES {$this->prefix}categories(id) ON DELETE SET NULL
        ) COMMENT = '{$metadata}'";
    }
    
    private function getTagsTableSQL(): string
    {
        $metadata = json_encode($this->getMetadata()[$this->prefix . 'tags']);
        
        return "CREATE TABLE IF NOT EXISTS {$this->prefix}tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL COMMENT '{\"type\": \"text\", \"label\": \"Name\", \"required\": true}',
            slug VARCHAR(50) UNIQUE NOT NULL COMMENT '{\"hidden\": true}'
        ) COMMENT = '{$metadata}'";
    }
    
    private function getPostTagsTableSQL(): string
    {
        return "CREATE TABLE IF NOT EXISTS {$this->prefix}post_tags (
            post_id INT NOT NULL,
            tag_id INT NOT NULL,
            PRIMARY KEY (post_id, tag_id),
            FOREIGN KEY (post_id) REFERENCES {$this->prefix}posts(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES {$this->prefix}tags(id) ON DELETE CASCADE
        )";
    }
    
    private function getCommentsTableSQL(): string
    {
        $metadata = json_encode($this->getMetadata()[$this->prefix . 'comments']);
        
        return "CREATE TABLE IF NOT EXISTS {$this->prefix}comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL COMMENT '{\"type\": \"select\", \"label\": \"Post\"}',
            author_name VARCHAR(100) NOT NULL COMMENT '{\"type\": \"text\", \"label\": \"Name\", \"required\": true}',
            author_email VARCHAR(255) NOT NULL COMMENT '{\"type\": \"email\", \"label\": \"Email\", \"required\": true}',
            author_url VARCHAR(255) COMMENT '{\"type\": \"url\", \"label\": \"Website\"}',
            content TEXT NOT NULL COMMENT '{\"type\": \"textarea\", \"label\": \"Comment\", \"required\": true}',
            status ENUM('pending', 'approved', 'spam') DEFAULT 'pending' COMMENT '{\"type\": \"select\", \"label\": \"Status\"}',
            parent_id INT NULL COMMENT '{\"type\": \"select\", \"label\": \"Reply To\"}',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES {$this->prefix}posts(id) ON DELETE CASCADE,
            FOREIGN KEY (parent_id) REFERENCES {$this->prefix}comments(id) ON DELETE CASCADE,
            INDEX idx_post (post_id),
            INDEX idx_status (status)
        ) COMMENT = '{$metadata}'";
    }
}
