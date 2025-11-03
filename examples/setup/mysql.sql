-- DynamicCRUD - MySQL Setup Script
-- Creates all tables needed for examples

-- Drop existing tables
DROP TABLE IF EXISTS post_tags;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS contacts;
DROP TABLE IF EXISTS advanced_inputs;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS audit_log;

-- Users table (Basic example, Virtual fields, Validation, i18n, Audit)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT '{"label": "Full Name", "placeholder": "Enter your full name", "minlength": 3}',
    email VARCHAR(255) NOT NULL UNIQUE COMMENT '{"type": "email", "label": "Email Address", "placeholder": "user@example.com", "tooltip": "We will never share your email", "autocomplete": "email"}',
    password VARCHAR(255) NOT NULL COMMENT '{"type": "password", "label": "Password", "minlength": 8, "placeholder": "Min 8 characters", "tooltip": "Use a strong password"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 
COMMENT='{"display_name": "User Management", "icon": "👥", "description": "Complete user administration", "color": "#667eea", "list_view": {"columns": ["id", "name", "email", "created_at"], "default_sort": "created_at DESC", "per_page": 25, "searchable": ["name", "email"], "actions": ["edit", "delete"]}}';

-- Ensure database uses utf8mb4
ALTER DATABASE test CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Categories table (Foreign keys)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT '{"label": "Category Name", "placeholder": "e.g., Technology, Business"}',
    description TEXT COMMENT '{"label": "Description", "placeholder": "Describe this category..."}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Posts table (Foreign keys, Hooks, M:N, Auto-behaviors)
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL COMMENT '{"label": "Post Title", "placeholder": "Enter an engaging title", "minlength": 5}',
    slug VARCHAR(255) UNIQUE COMMENT '{"label": "URL Slug", "placeholder": "auto-generated-from-title", "tooltip": "Leave empty to auto-generate", "pattern": "[a-z0-9-]+", "readonly": true}',
    content TEXT COMMENT '{"label": "Content", "placeholder": "Write your post content here..."}',
    status ENUM('draft', 'published') DEFAULT 'draft' COMMENT '{"type": "select", "label": "Status"}',
    published_at DATETIME COMMENT '{"type": "datetime-local", "label": "Publish Date", "tooltip": "Auto-set when status is published"}',
    category_id INT COMMENT '{"label": "Category"}',
    user_id INT COMMENT '{"label": "Author", "display_column": "name"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{"readonly": true, "label": "Created"}',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '{"readonly": true, "label": "Updated"}',
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
COMMENT='{"display_name": "Blog Posts", "icon": "📝", "color": "#28a745", "list_view": {"columns": ["id", "title", "status", "created_at"], "default_sort": "created_at DESC", "per_page": 3, "searchable": ["title", "content"], "actions": ["edit", "delete"]}, "filters": [{"field": "status", "type": "select", "label": "Estado", "options": ["draft", "published"]}, {"field": "created_at", "type": "daterange", "label": "Fecha de Creación"}], "behaviors": {"timestamps": {"created_at": "created_at", "updated_at": "updated_at"}, "sluggable": {"source": "title", "target": "slug", "unique": true, "separator": "-", "lowercase": true}}}';

-- Tags table (M:N)
CREATE TABLE tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE COMMENT '{"label": "Tag Name", "placeholder": "e.g., PHP, MySQL"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Post-Tags pivot table (M:N)
CREATE TABLE post_tags (
    post_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products table (File uploads)
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT '{"label": "Product Name", "placeholder": "Enter product name"}',
    description TEXT COMMENT '{"label": "Description", "placeholder": "Describe your product..."}',
    price DECIMAL(10, 2) NOT NULL COMMENT '{"type": "number", "step": "0.01", "min": 0, "label": "Price (USD)", "placeholder": "0.00"}',
    image VARCHAR(255) COMMENT '{"type": "file", "accept": "image/*", "max_size": 2097152, "label": "Product Image", "tooltip": "JPG, PNG or WebP. Max 2MB"}',
    category_id INT COMMENT '{"label": "Category"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}',
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contacts table (Metadata customization)
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL COMMENT '{"label": "Full Name", "placeholder": "Enter your full name", "minlength": 3}',
    email VARCHAR(255) NOT NULL COMMENT '{"type": "email", "label": "Email Address", "placeholder": "user@example.com", "tooltip": "We never share your email", "autocomplete": "email"}',
    phone VARCHAR(20) COMMENT '{"type": "tel", "label": "Phone Number", "placeholder": "+1 (555) 123-4567", "pattern": "[0-9+\\-\\s()]+", "autocomplete": "tel"}',
    website VARCHAR(255) COMMENT '{"type": "url", "label": "Website", "placeholder": "https://example.com", "tooltip": "Enter a valid URL"}',
    message TEXT COMMENT '{"label": "Your Message", "placeholder": "Tell us what you need...", "minlength": 10}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Advanced inputs table (Advanced input types demo)
CREATE TABLE advanced_inputs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    brand_color VARCHAR(7) COMMENT '{"type": "color", "label": "Brand Color", "placeholder": "#000000", "tooltip": "Pick your brand color"}',
    phone VARCHAR(20) COMMENT '{"type": "tel", "label": "Phone Number", "placeholder": "555-123-4567", "pattern": "[0-9]{3}-[0-9]{3}-[0-9]{4}"}',
    password VARCHAR(255) COMMENT '{"type": "password", "label": "Password", "minlength": 8, "placeholder": "Min 8 characters"}',
    search_query VARCHAR(255) COMMENT '{"type": "search", "label": "Search", "placeholder": "Search..."}',
    appointment_time TIME COMMENT '{"type": "time", "label": "Appointment Time"}',
    birth_week VARCHAR(10) COMMENT '{"type": "week", "label": "Birth Week"}',
    birth_month VARCHAR(7) COMMENT '{"type": "month", "label": "Birth Month"}',
    satisfaction INT COMMENT '{"type": "range", "label": "Satisfaction Level", "min": 0, "max": 100, "step": 10, "tooltip": "Rate from 0 to 100"}',
    email VARCHAR(255) COMMENT '{"type": "email", "label": "Email", "placeholder": "user@example.com", "autocomplete": "email"}',
    website VARCHAR(255) COMMENT '{"type": "url", "label": "Website", "placeholder": "https://example.com"}',
    notes TEXT COMMENT '{"label": "Notes", "placeholder": "Enter your notes here..."}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{"readonly": true, "label": "Created At"}'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Audit log table (Audit example)
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INT NOT NULL,
    action ENUM('create', 'update', 'delete') NOT NULL,
    user_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_user (user_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data
INSERT INTO categories (name, description) VALUES
('Technology', 'Tech news and articles'),
('Business', 'Business and finance'),
('Lifestyle', 'Health and lifestyle');

INSERT INTO tags (name) VALUES
('PHP'), ('MySQL'), ('JavaScript'), ('Tutorial'), ('News');

INSERT INTO users (name, email, password) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Jane Smith', 'jane@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO posts (title, slug, content, status, category_id, user_id) VALUES
('Getting Started with DynamicCRUD', 'getting-started-dynamiccrud', 'Learn how to use DynamicCRUD...', 'published', 1, 1),
('Advanced PHP Techniques', 'advanced-php-techniques', 'Explore advanced PHP patterns...', 'draft', 1, 2),
('MySQL Performance Tips', 'mysql-performance-tips', 'Optimize your database queries...', 'published', 1, 1),
('Building REST APIs', 'building-rest-apis', 'Create scalable APIs with PHP...', 'published', 1, 2),
('Docker for Developers', 'docker-for-developers', 'Containerize your applications...', 'draft', 1, 1);

INSERT INTO post_tags (post_id, tag_id) VALUES
(1, 1), (1, 2), (1, 4),
(2, 1), (2, 4);

INSERT INTO products (name, description, price, category_id) VALUES
('Laptop Pro', 'High-performance laptop', 1299.99, 1),
('Wireless Mouse', 'Ergonomic wireless mouse', 29.99, 1);

-- Add table metadata to contacts table with tabs
ALTER TABLE contacts COMMENT='{"display_name": "Contact Forms", "icon": "📧", "color": "#17a2b8", "list_view": {"columns": ["id", "name", "email", "created_at"], "default_sort": "created_at DESC", "per_page": 25, "searchable": ["name", "email", "message"], "actions": ["edit", "delete"]}, "form": {"layout": "tabs", "tabs": [{"name": "basic", "label": "Basic Info", "fields": ["name", "email"]}, {"name": "contact", "label": "Contact Details", "fields": ["phone", "website"]}, {"name": "message", "label": "Message", "fields": ["message"]}]}}';

-- Add table metadata to products table
ALTER TABLE products COMMENT='{"display_name": "Products", "icon": "🛍️", "color": "#fd7e14", "list_view": {"columns": ["id", "name", "price", "category_id"], "default_sort": "name ASC", "per_page": 20, "searchable": ["name", "description"], "actions": ["edit", "delete"]}}';


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


-- Authentication Example Setup
-- Run this SQL to set up the authentication example

-- Create users table with authentication metadata
DROP TABLE IF EXISTS auth_users;

CREATE TABLE auth_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) COMMENT = '{
    "display_name": "Users",
    "icon": "👤",
    "authentication": {
        "enabled": true,
        "identifier_field": "email",
        "password_field": "password",
        "registration": {
            "enabled": true,
            "auto_login": true,
            "default_role": "user",
            "required_fields": ["name", "email", "password"]
        },
        "login": {
            "enabled": true,
            "remember_me": true,
            "max_attempts": 5,
            "lockout_duration": 900,
            "session_lifetime": 7200
        }
    },
    "permissions": {
        "create": ["guest"],
        "read": ["owner", "admin"],
        "update": ["owner", "admin"],
        "delete": ["admin"]
    },
    "row_level_security": {
        "enabled": true,
        "owner_field": "id",
        "owner_can_edit": true,
        "owner_can_delete": false
    }
}';

-- Insert a test admin user (password: admin12345)
INSERT INTO auth_users (name, email, password, role) VALUES 
('Admin User', 'admin@example.com', '$2y$12$EkzVHPA16c10XIEtF/Mx4ugRJGli0rh5CapMB6gmW5jzHvqGqZfFi', 'admin');

-- Insert a test regular user (password: user12345)
INSERT INTO auth_users (name, email, password, role) VALUES 
('Regular User', 'user@example.com', '$2y$12$Oj1SrtKt4A4iHO40LL6GBu5k134Lhd7bCLlIEbtlHACpNZEOtGF3W', 'user');


-- Soft Deletes Example Setup
-- Run this SQL to set up the soft deletes example

-- Create posts table with soft deletes
DROP TABLE IF EXISTS soft_posts;

CREATE TABLE soft_posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    author VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL
) COMMENT = '{
    "display_name": "Posts (Soft Deletes)",
    "icon": "📝",
    "description": "Posts with soft delete support",
    "behaviors": {
        "soft_deletes": {
            "enabled": true,
            "column": "deleted_at"
        },
        "timestamps": {
            "created_at": "created_at",
            "updated_at": "updated_at"
        }
    },
    "list_view": {
        "columns": ["id", "title", "author", "created_at", "deleted_at"],
        "searchable": ["title", "content", "author"],
        "per_page": 10
    }
}';

-- Insert sample posts
INSERT INTO soft_posts (title, content, author) VALUES
('First Post', 'This is the first post content', 'John Doe'),
('Second Post', 'This is the second post content', 'Jane Smith'),
('Third Post', 'This is the third post content', 'Bob Johnson'),
('Fourth Post', 'This is the fourth post content', 'Alice Williams'),
('Fifth Post', 'This is the fifth post content', 'Charlie Brown');

-- Soft delete one post for testing
UPDATE soft_posts SET deleted_at = NOW() WHERE id = 3;


COMMIT;

SELECT 'Database setup completed successfully!' as message;