-- Payment Gateway Example Setup

-- Orders table
CREATE TABLE IF NOT EXISTS 30_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_id VARCHAR(255),
    payment_status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT = '{
    "display_name": "Pedidos",
    "icon": "💳",
    "behaviors": {
        "timestamps": {"created_at": "created_at", "updated_at": "updated_at"}
    }
}';

-- Configure columns
ALTER TABLE 30_orders 
MODIFY COLUMN customer_name VARCHAR(255)
COMMENT '{"label": "Cliente", "required": true}';

ALTER TABLE 30_orders 
MODIFY COLUMN customer_email VARCHAR(255)
COMMENT '{"type": "email", "label": "Email", "required": true}';

ALTER TABLE 30_orders 
MODIFY COLUMN total DECIMAL(10,2)
COMMENT '{"type": "number", "label": "Total", "min": 0.01, "step": "0.01", "required": true}';

ALTER TABLE 30_orders 
MODIFY COLUMN payment_method VARCHAR(50)
COMMENT '{"type": "select", "options": ["stripe", "paypal"], "label": "Método de Pago"}';

ALTER TABLE 30_orders 
MODIFY COLUMN payment_id VARCHAR(255)
COMMENT '{"hidden": true}';

ALTER TABLE 30_orders 
MODIFY COLUMN payment_status VARCHAR(50)
COMMENT '{"readonly": true, "label": "Estado del Pago"}';

-- Sample data
INSERT INTO 30_orders (customer_name, customer_email, total, status) VALUES
('Juan Pérez', 'juan@example.com', 99.99, 'pending'),
('María García', 'maria@example.com', 149.50, 'pending');
