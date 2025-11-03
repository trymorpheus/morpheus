-- RBAC Example Setup
-- This creates tables with permission metadata
-- Uses blog_ prefix to avoid conflicts with other examples

-- Blog posts table with RBAC metadata
CREATE TABLE IF NOT EXISTS blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
    content TEXT,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT = '{
    "display_name": "Blog Posts",
    "icon": "📝",
    "permissions": {
        "create": ["admin", "editor", "author"],
        "read": ["*"],
        "update": ["admin", "editor"],
        "delete": ["admin"]
    },
    "row_level_security": {
        "enabled": true,
        "owner_field": "user_id",
        "owner_can_edit": true,
        "owner_can_delete": false
    },
    "behaviors": {
        "timestamps": {
            "created_at": "created_at",
            "updated_at": "updated_at"
        },
        "sluggable": {
            "source": "title",
            "target": "slug",
            "unique": true
        }
    },
    "list_view": {
        "columns": ["id", "title", "status", "created_at"],
        "searchable": ["title", "content"],
        "per_page": 20
    }
}';

-- Blog users table
CREATE TABLE IF NOT EXISTS blog_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'author', 'user', 'guest') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) COMMENT = '{
    "display_name": "Users",
    "icon": "👤",
    "permissions": {
        "create": ["admin"],
        "read": ["admin", "editor"],
        "update": ["admin"],
        "delete": ["admin"]
    }
}';

-- Blog comments table with row-level security
CREATE TABLE IF NOT EXISTS blog_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES blog_users(id) ON DELETE CASCADE
) COMMENT = '{
    "display_name": "Comments",
    "icon": "💬",
    "permissions": {
        "create": ["admin", "editor", "author", "user"],
        "read": ["*"],
        "update": ["admin", "editor"],
        "delete": ["admin", "editor"]
    },
    "row_level_security": {
        "enabled": true,
        "owner_field": "user_id",
        "owner_can_edit": true,
        "owner_can_delete": true
    }
}';

-- Sample data
INSERT INTO blog_users (name, email, password, role) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Editor User', 'editor@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor'),
('Author User', 'author@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'author'),
('Regular User', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user')
ON DUPLICATE KEY UPDATE id=id;

INSERT INTO blog_posts (user_id, title, content, status) VALUES
(1, 'Welcome to the Blog', 'This is the first post', 'published'),
(2, 'Editor Post', 'Written by editor', 'published'),
(3, 'Author Post', 'Written by author', 'draft')
ON DUPLICATE KEY UPDATE id=id;
