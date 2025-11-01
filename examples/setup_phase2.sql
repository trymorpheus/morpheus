-- Base de datos extendida para Fase 2 - Claves Foráneas

USE test;

-- Tabla de categorías
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL COMMENT '{"label": "Nombre de categoría"}',
    description TEXT COMMENT '{"label": "Descripción"}'
);

-- Insertar categorías de ejemplo
INSERT INTO categories (name, description) VALUES
('Tecnología', 'Artículos sobre tecnología'),
('Diseño', 'Artículos sobre diseño'),
('Marketing', 'Artículos sobre marketing');

-- Tabla de posts con clave foránea
CREATE TABLE IF NOT EXISTS posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL COMMENT '{"label": "Título"}',
    content TEXT NOT NULL COMMENT '{"label": "Contenido"}',
    category_id INT NOT NULL COMMENT '{"label": "Categoría", "display_column": "name"}',
    author_id INT COMMENT '{"label": "Autor", "display_column": "name"}',
    published_date DATE COMMENT '{"label": "Fecha de publicación"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}',
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (author_id) REFERENCES users(id)
);

-- Insertar posts de ejemplo
INSERT INTO posts (title, content, category_id, author_id, published_date) VALUES
('Introducción a PHP 8', 'PHP 8 trae muchas mejoras...', 1, 1, '2024-01-15'),
('Diseño UX moderno', 'El diseño centrado en el usuario...', 2, 2, '2024-01-20');
