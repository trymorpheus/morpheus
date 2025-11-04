-- Real Estate Application Database Setup

-- Locales comerciales
CREATE TABLE IF NOT EXISTS locales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(255) NOT NULL COMMENT '{"type": "text", "label": "Título", "required": true}',
    descripcion TEXT COMMENT '{"type": "textarea", "label": "Descripción", "rows": 5}',
    direccion VARCHAR(255) COMMENT '{"type": "text", "label": "Dirección"}',
    barrio VARCHAR(100) COMMENT '{"type": "text", "label": "Barrio"}',
    metros_cuadrados INT COMMENT '{"type": "number", "label": "Metros Cuadrados", "min": 1}',
    precio_compra DECIMAL(10,2) COMMENT '{"type": "number", "label": "Precio Compra (€)", "step": "0.01"}',
    coste_reforma DECIMAL(10,2) COMMENT '{"type": "number", "label": "Coste Reforma (€)", "step": "0.01"}',
    precio_venta DECIMAL(10,2) COMMENT '{"type": "number", "label": "Precio Venta (€)", "step": "0.01"}',
    estado VARCHAR(50) DEFAULT 'comprado' COMMENT '{"hidden": true}',
    fotos TEXT COMMENT '{"type": "multiple_files", "label": "Fotos del Local", "accept": "image/*", "max_files": 20}',
    tiene_escaparate BOOLEAN DEFAULT FALSE COMMENT '{"type": "checkbox", "label": "Tiene Escaparate"}',
    altura_techo DECIMAL(3,2) COMMENT '{"type": "number", "label": "Altura Techo (m)", "step": "0.01"}',
    num_banos INT DEFAULT 0 COMMENT '{"type": "number", "label": "Número de Baños", "min": 0}',
    tiene_salida_humos BOOLEAN DEFAULT FALSE COMMENT '{"type": "checkbox", "label": "Salida de Humos"}',
    destacado BOOLEAN DEFAULT FALSE COMMENT '{"type": "checkbox", "label": "Destacar en Web"}',
    visible_web BOOLEAN DEFAULT TRUE COMMENT '{"type": "checkbox", "label": "Visible en Web"}',
    fecha_compra DATE COMMENT '{"type": "date", "label": "Fecha de Compra"}',
    fecha_venta DATE COMMENT '{"type": "date", "label": "Fecha de Venta"}',
    notas_internas TEXT COMMENT '{"type": "textarea", "label": "Notas Internas", "rows": 3}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) COMMENT = '{
    "display_name": "Locales Comerciales",
    "icon": "🏢",
    "list_view": {
        "searchable": ["titulo", "direccion", "barrio"],
        "per_page": 12,
        "default_sort": "created_at DESC",
        "list_columns": ["titulo", "barrio", "metros_cuadrados", "precio_venta", "estado"],
        "filters": [
            {"field": "estado", "type": "select", "label": "Estado", "options": ["comprado", "en_reforma", "en_venta", "vendido"]},
            {"field": "barrio", "type": "select", "label": "Barrio"}
        ]
    },
    "form": {
        "layout": "tabs",
        "tabs": [
            {
                "name": "Información Básica",
                "fields": ["titulo", "descripcion", "direccion", "barrio", "metros_cuadrados"]
            },
            {
                "name": "Precios",
                "fields": ["precio_compra", "coste_reforma", "precio_venta", "fecha_compra", "fecha_venta"]
            },
            {
                "name": "Características",
                "fields": ["tiene_escaparate", "altura_techo", "num_banos", "tiene_salida_humos"]
            },
            {
                "name": "Fotos",
                "fields": ["fotos"]
            },
            {
                "name": "Configuración",
                "fields": ["destacado", "visible_web", "notas_internas"]
            }
        ]
    },
    "behaviors": {
        "timestamps": true
    }
}';

-- Consultas de clientes
CREATE TABLE IF NOT EXISTS consultas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    local_id INT COMMENT '{"display_column": "titulo", "label": "Local de Interés"}',
    nombre VARCHAR(255) NOT NULL COMMENT '{"type": "text", "label": "Nombre Completo", "required": true}',
    email VARCHAR(255) NOT NULL COMMENT '{"type": "email", "label": "Email", "required": true}',
    telefono VARCHAR(50) COMMENT '{"type": "tel", "label": "Teléfono"}',
    mensaje TEXT COMMENT '{"type": "textarea", "label": "Mensaje", "rows": 4}',
    estado VARCHAR(50) DEFAULT 'nueva' COMMENT '{"hidden": true}',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (local_id) REFERENCES locales(id) ON DELETE SET NULL
) COMMENT = '{
    "display_name": "Consultas de Clientes",
    "icon": "📧",
    "list_view": {
        "searchable": ["nombre", "email", "telefono"],
        "per_page": 20,
        "default_sort": "created_at DESC",
        "list_columns": ["nombre", "email", "telefono", "estado", "created_at"],
        "filters": [
            {"field": "estado", "type": "select", "label": "Estado", "options": ["nueva", "contactado", "visita_programada", "cerrada"]}
        ]
    }
}';

-- Datos de ejemplo
INSERT INTO locales (titulo, descripcion, direccion, barrio, metros_cuadrados, precio_compra, coste_reforma, precio_venta, estado, tiene_escaparate, altura_techo, num_banos, destacado, visible_web, fecha_compra) VALUES
('Local Comercial en Gracia', 'Amplio local comercial en el corazón de Gracia. Ideal para restaurante o comercio. Totalmente reformado con acabados de primera calidad.', 'Carrer de Verdi, 45', 'Gracia', 120, 180000, 45000, 295000, 'en_venta', TRUE, 3.5, 2, TRUE, TRUE, '2024-01-15'),
('Local en Eixample', 'Local esquinero con gran visibilidad. Perfecto para oficina o showroom. Necesita reforma integral.', 'Passeig de Gracia, 123', 'Eixample', 85, 220000, 35000, 320000, 'en_reforma', TRUE, 3.2, 1, FALSE, FALSE, '2024-02-20'),
('Local en Poblenou', 'Local diáfano en zona en expansión. Ideal para coworking o galería de arte. Listo para entrar.', 'Carrer de Pujades, 78', 'Poblenou', 95, 150000, 25000, 245000, 'en_venta', FALSE, 4.0, 1, TRUE, TRUE, '2023-11-10'),
('Local en Sants', 'Local comercial con salida de humos. Perfecto para restaurante. Completamente equipado.', 'Carrer de Sants, 234', 'Sants', 110, 165000, 40000, 275000, 'vendido', TRUE, 3.0, 2, FALSE, FALSE, '2023-09-05');
