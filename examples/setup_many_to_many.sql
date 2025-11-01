-- Tablas para demostrar relaciones Muchos a Muchos

-- Tabla de tags
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE COMMENT '{"label": "Nombre del tag"}',
    slug VARCHAR(50) NOT NULL UNIQUE COMMENT '{"label": "Slug"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla pivote: posts_tags (relación M:N entre posts y tags)
CREATE TABLE IF NOT EXISTS posts_tags (
    post_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar algunos tags de ejemplo
INSERT INTO tags (name, slug) VALUES 
    ('PHP', 'php'),
    ('JavaScript', 'javascript'),
    ('MySQL', 'mysql'),
    ('Tutorial', 'tutorial'),
    ('Avanzado', 'avanzado'),
    ('Principiante', 'principiante')
ON DUPLICATE KEY UPDATE name=name;

-- Añadir metadato M:N a la tabla posts
-- Nota: Esto es un campo virtual que no existe en la BD, 
-- pero lo usaremos para definir la relación
ALTER TABLE posts 
MODIFY COLUMN content TEXT COMMENT '{"label": "Contenido"}';

-- El metadato M:N se definirá en el código PHP, no en la BD
