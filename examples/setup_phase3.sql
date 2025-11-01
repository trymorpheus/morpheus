-- Base de datos extendida para Fase 3 - Subida de Archivos

USE test;

-- Tabla de productos con imagen
CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL COMMENT '{"label": "Nombre del producto", "minlength": 3}',
    description TEXT COMMENT '{"label": "Descripción"}',
    price DECIMAL(10,2) NOT NULL COMMENT '{"label": "Precio", "min": 0}',
    image VARCHAR(255) NULL COMMENT '{"label": "Imagen", "type": "file", "accept": "image/*", "allowed_mimes": ["image/jpeg", "image/png", "image/gif", "image/webp"], "max_size": 2097152}',
    category_id INT COMMENT '{"label": "Categoría", "display_column": "name"}',
    stock INT DEFAULT 0 COMMENT '{"label": "Stock", "min": 0}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}',
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Insertar productos de ejemplo
INSERT INTO products (name, description, price, category_id, stock) VALUES
('Laptop HP', 'Laptop HP 15 pulgadas, 8GB RAM', 599.99, 1, 10),
('Mouse Logitech', 'Mouse inalámbrico ergonómico', 29.99, 1, 50);
