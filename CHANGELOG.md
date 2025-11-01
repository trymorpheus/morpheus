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

## [0.2.0] - 2025 - Fase 2

### Añadido
- Detección automática de claves foráneas desde INFORMATION_SCHEMA
- Generación de selects con datos de tablas relacionadas
- Sistema de caché con FileCacheStrategy
- Operación list() con paginación completa
- Operación delete() con prepared statements
- ListGenerator para renderizado de tablas
- Script clear_cache.php para limpieza manual
- Metadato "hidden" para campos autogenerados
- Metadato "display_column" para claves foráneas
- Ejemplos: posts.php (FK), categories.php (CRUD completo)

### Corregido
- Manejo correcto de valores NULL en campos opcionales
- Conversión automática de cadenas vacías a NULL
- Uso de PDO::PARAM_NULL en prepared statements
- Validación omitida para campos hidden

### Mejorado
- SecurityModule ahora maneja campos nullable
- CRUDHandler usa bindValue para tipos correctos
- FormGenerator renderiza selects para FK automáticamente
- SchemaAnalyzer con caché integrado

## [Unreleased] - Fase 3

### Planificado
- Validación cliente con JavaScript
- Subida de archivos
- Hooks/Eventos (beforeSave, afterCreate, etc.)
- Relaciones muchos-a-muchos
- Sistema de auditoría

---

**Leyenda**:
- `Añadido` para nuevas funcionalidades
- `Cambiado` para cambios en funcionalidades existentes
- `Obsoleto` para funcionalidades que serán removidas
- `Removido` para funcionalidades removidas
- `Corregido` para corrección de bugs
- `Seguridad` para vulnerabilidades corregidas
