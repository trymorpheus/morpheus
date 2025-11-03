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
