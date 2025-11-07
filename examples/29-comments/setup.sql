-- Comments table for blog posts
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    parent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_post (post_id),
    INDEX idx_status (status),
    INDEX idx_parent (parent_id),
    FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
) COMMENT '{"display_name":"Comments","icon":"💬"}';

-- Sample posts table (if not exists)
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample post
INSERT INTO posts (title, content) VALUES 
('Welcome to DynamicCRUD Comments!', 'This is a sample blog post to demonstrate the comment system. Try adding comments below!');
