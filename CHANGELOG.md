# Changelog - DynamicCRUD

Todos los cambios notables del proyecto se documentan en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

---

## [1.0.0] - 2025-01-31

### 🎉 Lanzamiento Inicial

Primera versión estable de DynamicCRUD con 4 fases completadas.

### ✨ Añadido

#### Fase 1: MVP
- Generación automática de formularios desde estructura SQL
- Validación servidor basada en tipos SQL y metadatos JSON
- Protección CSRF integrada
- Sanitización automática de datos
- Sentencias preparadas (PDO)
- Operaciones CREATE y UPDATE
- Clases: `DynamicCRUD`, `SchemaAnalyzer`, `FormGenerator`, `ValidationEngine`, `SecurityModule`, `CRUDHandler`

#### Fase 2: Características Intermedias
- Detección automática de claves foráneas
- Selects con datos de tablas relacionadas
- Sistema de caché (`FileCacheStrategy`)
- Operaciones READ con paginación y DELETE
- Manejo correcto de valores NULL
- Metadatos: `hidden`, `display_column`
- Clase: `ListGenerator`

#### Fase 3: Validación Cliente y Archivos
- Validación JavaScript en tiempo real (`dynamiccrud.js`)
- Subida de archivos con validación MIME y tamaño
- Preview de imágenes
- Tooltips informativos
- Mejoras de accesibilidad (ARIA, navegación por teclado)
- Mensajes mejorados con animaciones
- Indicadores de carga
- Clase: `FileUploadHandler`
- Assets: `dynamiccrud.css`, `dynamiccrud.js`

#### Fase 4: Características Avanzadas
- Sistema de Hooks/Eventos (10 hooks disponibles)
- Transacciones automáticas con rollback
- Soporte para campos ENUM
- Relaciones muchos-a-muchos (M:N)
- Sistema de auditoría opcional
- Clase: `AuditLogger`

#### Documentación
- `README.md` - Introducción y uso básico
- `docs/CUSTOMIZATION.md` - Guía de personalización
- `docs/HOOKS.md` - Sistema de hooks con ejemplos
- `docs/MANY_TO_MANY.md` - Relaciones M:N
- `LIMITATIONS.md` - Análisis de limitaciones
- `BUGS.md` - Registro de bugs
- `PROJECT_SUMMARY.md` - Resumen completo del proyecto
- `CHANGELOG.md` - Este archivo

#### Ejemplos
- `examples/index.php` - CRUD básico (users)
- `examples/posts.php` - Claves foráneas
- `examples/categories.php` - CRUD completo con DELETE
- `examples/products.php` - Subida de archivos
- `examples/contacts.php` - Validación cliente + UX
- `examples/hooks_demo.php` - Sistema de hooks
- `examples/many_to_many_demo.php` - Relaciones M:N
- `examples/audit_demo.php` - Sistema de auditoría

#### Scripts SQL
- `examples/setup.sql` - Tablas básicas (Fase 1)
- `examples/setup_phase2.sql` - Claves foráneas (Fase 2)
- `examples/setup_phase3.sql` - Tabla products con archivos (Fase 3)
- `examples/setup_phase3_ux.sql` - Tabla contacts con UX (Fase 3)
- `examples/setup_phase4.sql` - Columnas para hooks (Fase 4)
- `examples/setup_many_to_many.sql` - Tablas M:N (Fase 4)
- `examples/setup_audit.sql` - Tabla de auditoría (Fase 4)

### 🔧 Corregido

- **BUG-001**: Token CSRF inválido - Regeneración prematura del token
- **BUG-002**: Extensión fileinfo no habilitada
- **BUG-003**: Ruta de archivo con path absoluto
- **BUG-004**: Campos nullable guardaban cadenas vacías
- **BUG-005**: display_errors deshabilitado
- **BUG-006**: Spinner de carga no visible (caché del navegador)

### 🔒 Seguridad

- Protección CSRF con tokens por sesión
- Sanitización de todos los inputs
- Validación MIME real con finfo
- Sentencias preparadas para prevenir SQL injection
- Nombres de archivo únicos para prevenir sobrescritura
- Transacciones para integridad de datos

### 📊 Estadísticas

- **Clases PHP**: 10
- **Líneas de código**: ~3,500
- **Ejemplos funcionales**: 8
- **Documentos**: 7
- **Bugs resueltos**: 6 (100%)
- **Tiempo de desarrollo**: < 1 día
- **Completitud**: 98.75%

---

## [Unreleased] - Fase 5 (Futuro)

### 🔮 Planificado

#### Alta Prioridad
- [ ] Soporte PostgreSQL (patrón Adapter)
- [ ] Tests automatizados (PHPUnit)
- [ ] Campos virtuales (confirmación password)

#### Media Prioridad
- [ ] UI avanzada para M:N (checkboxes, búsqueda)
- [ ] Internacionalización (i18n)
- [ ] Sistema de plantillas

#### Baja Prioridad
- [ ] Rate limiting
- [ ] Permisos granulares
- [ ] Soporte SQL Server
- [ ] API REST automática

---

## Tipos de Cambios

- `✨ Añadido` - Nuevas funcionalidades
- `🔧 Corregido` - Corrección de bugs
- `🔄 Cambiado` - Cambios en funcionalidades existentes
- `🗑️ Eliminado` - Funcionalidades eliminadas
- `🔒 Seguridad` - Mejoras de seguridad
- `📚 Documentación` - Cambios en documentación
- `⚡ Rendimiento` - Mejoras de rendimiento

---

**Mantenido por**: Mario Raúl Carbonell Martínez  
**Última actualización**: 2025-01-31
