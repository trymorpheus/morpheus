# Project Structure

## Directory Organization

### Root Level
```
morpheus/
├── src/                    # Core library source code (PSR-4)
├── tests/                  # PHPUnit test suite
├── examples/               # 29 working examples with documentation
├── docs/                   # 25+ technical guides
├── bin/                    # CLI executable (morpheus)
├── install/                # One-click installer (web UI)
├── themes/                 # Built-in theme templates
├── templates/              # Blade-like template files
├── cache/                  # Template and schema cache
├── uploads/                # File upload storage
├── vendor/                 # Composer dependencies
└── local_docs/             # Internal planning documents
```

## Core Source Structure (src/)

### Main Classes (Root Level)
- **Morpheus.php** - Main entry point, orchestrates all components
- **CRUDHandler.php** - Handles form submission and data processing
- **FormGenerator.php** - Generates HTML forms from schema
- **ListGenerator.php** - Generates data tables with search/filter
- **SchemaAnalyzer.php** - Analyzes database schema and metadata
- **ValidationEngine.php** - Server-side validation logic
- **ValidationRulesEngine.php** - Advanced validation rules
- **FileUploadHandler.php** - File upload processing
- **AuditLogger.php** - Change tracking and logging
- **NotificationManager.php** - Email notifications and webhooks
- **GlobalMetadata.php** - Centralized configuration storage
- **VirtualField.php** - Non-database fields (password confirmation, etc.)
- **SecurityModule.php** - Security utilities
- **BrandingManager.php** - White-label branding
- **ThemeManager.php** - Legacy theme manager (v2.9)

### Admin/ - Admin Panel
- **AdminPanel.php** - Complete admin interface with sidebar, dashboard, breadcrumbs

### API/ - REST API
- **RestAPIGenerator.php** - Automatic REST API with JWT authentication

### Cache/ - Caching System
- **CacheStrategy.php** - Cache interface
- **FileCacheStrategy.php** - File-based cache implementation
- **QueryCache.php** - Query result caching

### CLI/ - Command Line Interface
- **Application.php** - CLI application orchestrator
- **Commands/** - 20+ CLI commands (init, install, generate, export, import, etc.)

### Comments/ - Comment System
- **CommentManager.php** - Comment CRUD and moderation
- **CommentRenderer.php** - Comment UI rendering with nested replies

### ContentTypes/ - Content Type System
- **ContentType.php** - Interface for all content types
- **ContentTypeManager.php** - Manages content type lifecycle
- **BlogContentType.php** - Complete blog implementation (posts, categories, tags)

### Database/ - Database Adapters
- **DatabaseAdapter.php** - Database interface
- **MySQLAdapter.php** - MySQL-specific implementation
- **PostgreSQLAdapter.php** - PostgreSQL-specific implementation

### Export/ - Data Export/Import
- **ExportManager.php** - CSV export functionality
- **ImportManager.php** - CSV import with validation

### Frontend/ - Public-Facing Pages
- **FrontendRouter.php** - Routes public URLs to content
- **FrontendRenderer.php** - Renders public pages (home, single, archive)
- **SEOManager.php** - Meta tags, Open Graph, Schema.org, sitemap, RSS
- **Route.php** - Value object for route data

### I18n/ - Internationalization
- **Translator.php** - Multi-language support (EN, ES, FR)
- **locales/** - Translation files for each language

### Installer/ - One-Click Installer
- **InstallerWizard.php** - Main installation orchestrator
- **SystemChecker.php** - Validates system requirements
- **DatabaseSetup.php** - Database connection and table creation
- **ConfigGenerator.php** - Generates config.php file

### Media/ - Media Library
- **MediaLibrary.php** - File upload and management
- **ImageEditor.php** - Image manipulation (resize, crop, thumbnails)
- **MediaBrowser.php** - Visual file browsing interface

### Metadata/ - Table Metadata
- **TableMetadata.php** - Parses and manages table/column metadata from JSON comments

### Migration/ - WordPress Migration
- **WordPressMigrator.php** - Main migration orchestrator
- **WXRParser.php** - WordPress XML (WXR) parser
- **ContentMapper.php** - Maps WP content to DynamicCRUD
- **MediaDownloader.php** - Downloads remote media files

### Security/ - Authentication & Authorization
- **AuthenticationManager.php** - User registration, login, logout, password reset
- **PermissionManager.php** - RBAC with table and row-level permissions

### Template/ - Template Engine
- **TemplateEngine.php** - Template interface
- **BladeTemplate.php** - Blade-like template engine with caching

### Theme/ - Theme System
- **Theme.php** - Interface for all themes
- **ThemeManager.php** - Manages theme lifecycle and activation
- **AbstractTheme.php** - Base class for themes
- **Themes/** - Built-in theme implementations (Minimal, Modern, Classic)

### UI/ - UI Components
- **Components.php** - 15 reusable UI components (alert, badge, button, card, modal, tabs, etc.)

### Workflow/ - Workflow Engine
- **WorkflowEngine.php** - State management with transitions and permissions
- **WorkflowTemplate.php** - Workflow configuration templates

## Examples Structure (examples/)

### Organized by Feature
- **01-basic/** - Basic CRUD operations
- **02-relationships/** - Foreign keys and many-to-many
- **03-customization/** - Metadata and file uploads
- **04-advanced/** - Hooks, validation, virtual fields
- **05-features/** - Audit, i18n, templates
- **06-table-metadata/** - Table metadata system
- **07-rbac/** - Role-based access control
- **08-authentication/** - Login, register, password reset
- **09-soft-deletes/** - Soft delete functionality
- **10-validation-rules/** - Advanced validation
- **11-notifications/** - Email and webhooks
- **12-export-import/** - CSV export/import
- **13-sql-dump/** - SQL dump and import
- **14-global-config/** - Global configuration
- **15-multiple-files/** - Multiple file upload
- **16-theme-integration/** - Theme integration
- **17-rest-api/** - REST API generation
- **18-admin-panel/** - Admin panel
- **19-workflow/** - Workflow engine
- **20-ui-components/** - UI components library
- **21-branding/** - Branding customization
- **22-advanced-workflows/** - Advanced workflow patterns
- **23-real-estate-app/** - Complete real estate application
- **24-blog-cms/** - Blog CMS (WordPress alternative)
- **25-themes/** - Theme showcase
- **26-wordpress-migration/** - WordPress migration tool
- **27-installer/** - One-click installer
- **28-media-library/** - Media library
- **29-comments/** - Comment system

### Example Structure
Each example includes:
- **index.php** - Main example code
- **setup.sql** - Database schema (if needed)
- **README.md** - Documentation and explanation
- **SUMMARY.md** - Quick overview (for newer examples)

## Documentation Structure (docs/)

### Getting Started
- **QUICKSTART.md** - 5-minute tutorial
- **MIGRATION.md** - Version upgrade guide
- **BEST_PRACTICES.md** - Production patterns

### Core Features
- **CUSTOMIZATION.md** - Metadata options
- **HOOKS.md** - Lifecycle hooks
- **VIRTUAL_FIELDS.md** - Virtual field system
- **MANY_TO_MANY.md** - M:N relationships
- **TEMPLATES.md** - Template engine
- **I18N.md** - Internationalization
- **PERFORMANCE.md** - Optimization guide

### Advanced Features
- **TABLE_METADATA.md** - Table metadata system
- **RBAC.md** - Authentication and permissions
- **VALIDATION_RULES.md** - Advanced validation
- **NOTIFICATIONS.md** - Email and webhooks
- **GLOBAL_METADATA.md** - Global configuration
- **CLI.md** - Command-line interface
- **REST_API.md** - REST API generation
- **WORKFLOW.md** - Workflow engine
- **UI_COMPONENTS.md** - UI components library

### v4.0 Features
- **CONTENT_TYPES.md** - Content type system
- **FRONTEND_RENDERING.md** - Public pages
- **SEO.md** - SEO optimization
- **THEMES.md** - Theme system
- **WORDPRESS_MIGRATION.md** - WordPress migration

### Refactoring
- **REFACTORING_PATTERNS.md** - Code quality improvements

## Test Structure (tests/)

### Test Organization
- **Unit Tests** - One test file per class (e.g., DynamicCRUDTest.php)
- **Integration Tests** - Multi-class interactions
- **Feature Tests** - End-to-end functionality

### Test Categories
- Core CRUD operations
- Database adapters (MySQL, PostgreSQL)
- Form generation and validation
- Authentication and permissions
- Content types and themes
- Export/import functionality
- CLI commands
- UI components
- Workflow engine

### Test Utilities
- **TestHelper.php** - Shared test utilities
- **fixtures/** - Test data and templates

## Installer Structure (install/)

### Web Installer
- **index.php** - Main installer entry point
- **templates/** - 8 step templates (welcome, system check, database, site info, content type, theme, install, success)
- **assets/** - CSS and JavaScript for installer UI

## Theme Structure (themes/)

### Theme Organization
Each theme has:
- **config.php** - Theme configuration
- **templates/** - Template files (home, single, archive, etc.)
- **assets/** - CSS and JavaScript (inline in templates)

### Built-in Themes
- **minimal/** - Clean, simple design
- **modern/** - Modern design with gradients
- **classic/** - Traditional blog design

## Architectural Patterns

### 1. Adapter Pattern
Used for database abstraction:
- DatabaseAdapter interface
- MySQLAdapter and PostgreSQLAdapter implementations
- Allows easy addition of new databases

### 2. Strategy Pattern
Used for caching:
- CacheStrategy interface
- FileCacheStrategy implementation
- Allows pluggable cache backends

### 3. Template Method Pattern
Used for themes:
- AbstractTheme base class
- Concrete theme implementations override specific methods

### 4. Factory Pattern
Used for content types:
- ContentTypeManager creates content type instances
- Allows dynamic content type registration

### 5. Observer Pattern
Used for hooks/events:
- DynamicCRUD fires events at lifecycle points
- User code registers callbacks to observe events

### 6. Facade Pattern
Used for main API:
- Morpheus class provides simple interface
- Orchestrates complex interactions between components

### 7. Repository Pattern
Used for data access:
- CRUDHandler abstracts database operations
- Separates business logic from data access

## Component Relationships

### Core Flow
```
Morpheus (Facade)
    ├── SchemaAnalyzer (reads database schema)
    ├── FormGenerator (creates HTML forms)
    ├── ListGenerator (creates data tables)
    ├── CRUDHandler (processes submissions)
    │   ├── ValidationEngine (validates data)
    │   ├── ValidationRulesEngine (advanced rules)
    │   ├── FileUploadHandler (handles files)
    │   ├── AuditLogger (logs changes)
    │   └── NotificationManager (sends notifications)
    └── DatabaseAdapter (MySQL/PostgreSQL)
```

### Universal CMS Flow
```
FrontendRouter (routes URLs)
    └── FrontendRenderer (renders pages)
        ├── ContentTypeManager (manages content)
        ├── ThemeManager (manages themes)
        ├── SEOManager (SEO optimization)
        └── CommentManager (manages comments)
```

### Admin Panel Flow
```
AdminPanel (main interface)
    ├── Morpheus (CRUD operations)
    ├── ListGenerator (data tables)
    ├── FormGenerator (forms)
    └── Components (UI elements)
```

## Configuration Files

### Root Level
- **composer.json** - PHP dependencies and autoloading
- **phpunit.xml** - PHPUnit configuration
- **docker-compose.yml** - Docker setup for MySQL/PostgreSQL
- **.gitignore** - Git ignore rules

### Generated
- **config.php** - Generated by installer (database credentials, site info)
- **cache/** - Generated template and schema cache files

## Data Storage

### Database Tables
- **User tables** - Defined by user schema
- **_metadata** - Global configuration storage
- **_audit_log** - Change tracking (if enabled)
- **_workflow_history** - Workflow transitions (if enabled)
- **Content type tables** - Created by content type installer (e.g., blog: posts, categories, tags, comments)

### File Storage
- **uploads/** - User-uploaded files
- **cache/templates/** - Compiled template cache
- **cache/*.cache** - Schema metadata cache

## Naming Conventions

### Classes
- PascalCase (e.g., DynamicCRUD, FormGenerator)
- Descriptive names indicating purpose
- Manager suffix for orchestrators
- Engine suffix for processors
- Generator suffix for creators
- Handler suffix for processors

### Files
- Match class names (e.g., DynamicCRUD.php)
- Lowercase with hyphens for non-class files (e.g., docker-compose.yml)

### Namespaces
- PSR-4: Morpheus\SubNamespace
- Matches directory structure

### Database
- Lowercase with underscores (e.g., user_roles)
- Singular table names preferred
- Metadata tables prefixed with underscore (e.g., _metadata)

## Dependencies

### Required
- PHP 8.0+
- PDO extension
- fileinfo extension
- json extension
- MySQL 5.7+ or PostgreSQL 12+

### Development
- PHPUnit 9.5+ or 10.0+

### Zero Runtime Dependencies
- No external PHP libraries required
- Pure PHP implementation
- Self-contained and portable
