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

COMMIT;

SELECT 'Database setup completed successfully!' as message;
