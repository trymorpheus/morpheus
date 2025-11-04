-- Workflow Example: Order Management System
-- Drop tables if exist
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS _workflow_history;

-- Create orders table with workflow
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL COMMENT '{"label": "Cliente"}',
    product VARCHAR(255) NOT NULL COMMENT '{"label": "Producto"}',
    amount DECIMAL(10,2) NOT NULL COMMENT '{"label": "Monto", "type": "number", "step": "0.01", "min": 0}',
    status VARCHAR(50) NOT NULL DEFAULT 'pending' COMMENT '{"label": "Estado", "readonly": true}',
    notes TEXT COMMENT '{"label": "Notas"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '{"hidden": true}'
) COMMENT = '{
    "display_name": "Orders",
    "icon": "🛒",
    "list_view": {
        "searchable": ["customer_name", "product"],
        "per_page": 20
    },
    "behaviors": {
        "timestamps": {
            "created_at": "created_at",
            "updated_at": "updated_at"
        }
    }
}';

-- Insert sample orders
INSERT INTO orders (customer_name, product, amount, status, notes) VALUES
('John Doe', 'Laptop', 1299.99, 'pending', 'Urgent delivery'),
('Jane Smith', 'Mouse', 29.99, 'pending', NULL),
('Bob Johnson', 'Keyboard', 89.99, 'processing', 'Gift wrap requested'),
('Alice Brown', 'Monitor', 399.99, 'shipped', 'Fragile - handle with care'),
('Charlie Wilson', 'Headphones', 149.99, 'delivered', 'Customer very satisfied');
