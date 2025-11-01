# Changelog

Todos los cambios notables del proyecto DynamicCRUD serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Versionado Semántico](https://semver.org/lang/es/).

## [0.1.0] - 2025 - Fase 1 MVP

### Añadido
- SchemaAnalyzer para introspección de tablas MySQL
- FormGenerator para generación automática de formularios HTML
- ValidationEngine con validación de tipos SQL y metadatos
- CRUDHandler con operaciones CREATE y UPDATE
- SecurityModule con protección CSRF y sanitización
- Soporte para metadatos JSON en comentarios de columnas
- Mapeo automático de tipos SQL a inputs HTML
- Validación de email y URL desde metadatos
- Ejemplo funcional con listado y edición de usuarios
- Tests para SchemaAnalyzer y ValidationEngine
- Documentación completa (README, INSTALL, PROYECTO)
- Autoloader PSR-4 simple

### Seguridad
- Protección CSRF con tokens por sesión
- Sentencias preparadas PDO (prevención SQL injection)
- Sanitización automática de inputs
- Escape de outputs con htmlspecialchars (prevención XSS)
- Whitelist de columnas (prevención mass assignment)

### Tipos Soportados
- INT, BIGINT, SMALLINT, TINYINT → number
- VARCHAR, CHAR → text
- TEXT, LONGTEXT, MEDIUMTEXT → textarea
- DATE → date
- DATETIME, TIMESTAMP → datetime-local
- Metadatos: email → email, url → url

### Validaciones Implementadas
- Campos requeridos (NOT NULL)
- Longitud máxima (CHARACTER_MAXIMUM_LENGTH)
- Tipos numéricos (FILTER_VALIDATE_INT)
- Email (FILTER_VALIDATE_EMAIL)
- URL (FILTER_VALIDATE_URL)
- Fechas (strtotime)

## [Unreleased] - Fase 2

### Planificado
- Detección y manejo de claves foráneas
- Validación cliente con JavaScript
- Sistema de caché (APCu/archivos)
- Subida de archivos
- Operaciones READ (listado con paginación) y DELETE
- Carga AJAX para selects grandes

---

**Leyenda**:
- `Añadido` para nuevas funcionalidades
- `Cambiado` para cambios en funcionalidades existentes
- `Obsoleto` para funcionalidades que serán removidas
- `Removido` para funcionalidades removidas
- `Corregido` para corrección de bugs
- `Seguridad` para vulnerabilidades corregidas
