-- Tabla de auditoría para registrar cambios

CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(64) NOT NULL COMMENT 'Tabla afectada',
    record_id INT NOT NULL COMMENT 'ID del registro',
    action ENUM('CREATE', 'UPDATE', 'DELETE') NOT NULL COMMENT 'Acción realizada',
    user_id INT NULL COMMENT 'ID del usuario que realizó la acción',
    user_ip VARCHAR(45) NULL COMMENT 'IP del usuario',
    old_values JSON NULL COMMENT 'Valores anteriores (solo UPDATE)',
    new_values JSON NULL COMMENT 'Valores nuevos (CREATE y UPDATE)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha y hora',
    INDEX idx_table_record (table_name, record_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Registro de auditoría de cambios';
