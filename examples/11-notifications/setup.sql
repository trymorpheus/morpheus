-- Setup for Notifications Example

USE test;

-- Drop tables if exist
DROP TABLE IF EXISTS notif_orders;
DROP TABLE IF EXISTS notif_contacts;

-- Orders table with email notifications
CREATE TABLE notif_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    product VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) COMMENT = '{
    "display_name": "Orders",
    "icon": "🛒",
    "notifications": {
        "on_create": {
            "email": ["admin@example.com"],
            "subject": "New Order Received",
            "template": "<h2>New Order #{{id}}</h2><p>Customer: {{data.customer_name}}</p><p>Product: {{data.product}}</p><p>Amount: ${{data.amount}}</p>"
        },
        "on_update": {
            "email": ["admin@example.com"],
            "subject": "Order Updated",
            "fields": ["status"]
        }
    }
}';

-- Contacts table with webhooks
CREATE TABLE notif_contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    message TEXT,
    status ENUM('new', 'contacted', 'closed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) COMMENT = '{
    "display_name": "Contact Forms",
    "icon": "📧",
    "webhooks": [
        {
            "event": "on_create",
            "url": "https://webhook.site/unique-id",
            "method": "POST",
            "headers": {
                "Authorization": "Bearer demo-token"
            }
        }
    ]
}';

-- Sample data
INSERT INTO notif_orders (customer_name, customer_email, product, amount, status) VALUES
('John Doe', 'john@example.com', 'Laptop', 1200.00, 'pending'),
('Jane Smith', 'jane@example.com', 'Mouse', 25.00, 'completed');

INSERT INTO notif_contacts (name, email, phone, message, status) VALUES
('Alice Johnson', 'alice@example.com', '555-0001', 'I need help with my order', 'new'),
('Bob Wilson', 'bob@example.com', '555-0002', 'Question about pricing', 'contacted');
