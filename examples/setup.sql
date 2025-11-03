-- Base de datos de ejemplo para DynamicCRUD

CREATE DATABASE IF NOT EXISTS test;
USE test;

-- Tabla de ejemplo: users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL COMMENT '{"label": "Nombre completo"}',
    email VARCHAR(255) NOT NULL COMMENT '{"type": "email", "label": "Correo electrónico"}',
    website VARCHAR(255) COMMENT '{"type": "url", "label": "Sitio web"}',
    age INT COMMENT '{"label": "Edad"}',
    bio TEXT COMMENT '{"label": "Biografía"}',
    birth_date DATE COMMENT '{"label": "Fecha de nacimiento"}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{"hidden": true}'
);

-- Datos de ejemplo
INSERT INTO users (name, email, website, age, bio, birth_date) VALUES
('Juan Pérez', 'juan@example.com', 'https://juan.com', 30, 'Desarrollador web', '1993-05-15'),
('María García', 'maria@example.com', 'https://maria.com', 25, 'Diseñadora UX', '1998-08-22');