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
