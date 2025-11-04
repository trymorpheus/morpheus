# Changelog

All notable changes to DynamicCRUD will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.4.0] - 2025-01-XX

### 🎉 CLI Enhancements Release

Powerful new CLI commands for webhook management, metadata operations, and database testing.

### ✨ Added

#### New CLI Commands
- **test:connection** - Test database connectivity and display connection info
- **webhook:configure** - Configure webhooks for tables from command line
- **test:webhook** - Test webhook connectivity with real HTTP requests
- **metadata:export** - Export table and column metadata to JSON file
- **metadata:import** - Import metadata from JSON file for backup/migration

#### CLI Improvements
- Reorganized help menu with command categories
- Better error messages and status indicators
- SSL verification disabled for development webhooks
- Support for command options (--output, --event)

#### Testing
- **CLICommandsTest** - 3 integration tests (100% passing)
- All existing tests passing (280/280)

#### Documentation
- Updated CLI.md with 5 new commands
- Added 2 new workflows (webhook setup, metadata migration)
- Enhanced troubleshooting section

### 🔧 Changed
- CLI help output now organized by categories
- Application.php registers 10 commands (5 new)

### 📊 Statistics
- **5 new CLI commands**
- **3 new tests** (100% passing)
- **280 total tests** (100% passing, 90% coverage)
- **10 total CLI commands**

---

## [2.3.0] - 2025-01-XX

### 🎉 Notifications & Webhooks Release

Automatic email notifications and webhook triggers on CRUD events.

### ✨ Added

#### Notifications System
- **NotificationManager** class for email and webhook handling
- **Email notifications** with template placeholders (`{{id}}`, `{{data.field}}`)
- **Webhook triggers** with custom headers and methods (GET, POST, PUT, DELETE)
- **Event-based triggers** (on_create, on_update, on_delete)
- **Field-specific notifications** - Only notify when specific fields change
- **Multiple recipients** - Send to multiple email addresses
- **Multiple webhooks** - Trigger multiple endpoints per event
- **Non-blocking execution** - Errors don't prevent CRUD operations
- **Template placeholders** - Dynamic content in subject and body
- **JSON payload** - Structured webhook data with event, table, id, data, timestamp

#### Table Metadata Extensions
- `hasNotifications()` method in TableMetadata
- `getNotificationConfig()` method in TableMetadata
- Support for `notifications` and `webhooks` in table COMMENT

#### Integration
- Automatic notification triggers in CRUDHandler after CRUD events
- Email sent via PHP `mail()` function (customizable)
- Webhooks called via cURL with 10-second timeout
- Graceful error handling with @ suppression

#### Examples
- `examples/11-notifications/` - Complete notifications examples
  - `email-notifications.php` - Email notifications on order creation/update
  - `webhooks.php` - Webhook triggers on contact form submission
  - `setup.sql` - Database setup with notif_ prefixed tables

#### Testing
- **NotificationManagerTest** - 7 tests (100% passing)
  - Email notification tests (3)
  - Webhook trigger tests (2)
  - Edge case tests (2)
- All existing tests passing (277/277)

#### Documentation
- `docs/NOTIFICATIONS.md` - Complete notifications guide
- Email configuration examples
- Webhook configuration with authentication
- Security considerations
- Testing with webhook.site
- Custom email handler examples
- Performance considerations (async processing)

### 🔧 Changed
- CRUDHandler now triggers notifications after afterCreate, afterUpdate, afterDelete hooks
- NotificationManager instantiated only when table has notification config

### 📊 Statistics
- **1 new class** (NotificationManager)
- **2 new examples** in 11-notifications/
- **7 new tests** (100% passing)
- **277 total tests** (100% passing, 90% coverage)
- **2 new TableMetadata methods**
- **Notification features**: 2 (email, webhooks)
- **Event types**: 3 (on_create, on_update, on_delete)

---

## [2.2.0] - 2025-01-XX

### 🎉 Validation Rules & Business Logic Release

Advanced validation rules and business logic configured entirely through table metadata.

### ✨ Added

#### Validation Rules
- **ValidationRulesEngine** class for advanced validation
- **unique_together** - Composite unique constraints (e.g., SKU + Category)
- **required_if** - Conditional required fields based on other field values
- **conditional** - Dynamic min/max validation with conditions
- Support for complex expressions in conditions (`>`, `<`, `>=`, `<=`, `==`, `!=`)
- Automatic integration with CRUDHandler validation flow

#### Business Rules
- **max_records_per_user** - Limit records per user (quota management)
- **require_approval** - Approval workflow with configurable roles
- **owner_field** - Configurable owner field for user-based limits
- Integration with PermissionManager for user context

#### Table Metadata Extensions
- `hasValidationRules()` method in TableMetadata
- `getValidationRules()` method in TableMetadata
- `hasBusinessRules()` method in TableMetadata
- `getBusinessRules()` method in TableMetadata
- `getAllRules()` method for combined rules access

#### Examples
- `examples/10-validation-rules/` - Complete validation rules examples
  - `unique-together.php` - Composite unique constraints demo
  - `required-if.php` - Conditional required fields demo
  - `conditional.php` - Dynamic validation demo
  - `business-rules.php` - Business logic demo
  - `setup.sql` - Database setup with vr_ prefixed tables
  - `README.md` - Examples guide

#### Testing
- **ValidationRulesEngineTest** - 12 tests (100% passing)
  - Unique together tests (3)
  - Required if tests (3)
  - Conditional validation tests (3)
  - Business rules tests (2)
  - Combined rules test (1)
- All existing tests passing (243/243)

#### Documentation
- `docs/VALIDATION_RULES.md` - Complete validation rules guide
- Configuration examples for all rule types
- Integration examples with RBAC and hooks
- Error handling and i18n support

### 🔧 Changed
- CRUDHandler now validates advanced rules after basic validation
- Validation errors include field-specific and global messages
- Transaction rollback on any validation failure

### 📊 Statistics
- **1 new class** (ValidationRulesEngine)
- **4 new examples** in 10-validation-rules/
- **12 new tests** (100% passing)
- **243 total tests** (100% passing, 90% coverage)
- **5 new TableMetadata methods**
- **Validation features**: 3 (unique_together, required_if, conditional)
- **Business rules**: 2 (max_records_per_user, require_approval)

---

## [2.1.0] - 2024-12-XX

### 🎉 Authentication & RBAC Release

Complete authentication and authorization system with metadata-driven configuration.

### ✨ Added

#### Authentication System
- **AuthenticationManager** class for user authentication
- User registration with automatic password hashing (bcrypt)
- Secure login with password verification
- Rate limiting (5 attempts, 15-minute lockout)
- Session management with remember me support
- Logout functionality with session cleanup
- CSRF token filtering in registration
- Auto-login after registration (configurable)

#### RBAC (Role-Based Access Control)
- **PermissionManager** class for authorization
- Table-level permissions (create, read, update, delete)
- Row-level security (owner-based access control)
- Wildcard permissions (`*` for public access)
- Permission checks in forms, lists, and handlers
- Dynamic action button filtering based on permissions
- Owner field auto-inclusion in SELECT queries

#### Soft Deletes
- **Soft delete support** - Mark records as deleted without removing them
- `delete()` method performs soft delete when enabled
- `restore()` method to recover soft-deleted records
- `forceDelete()` method to permanently remove records
- Automatic filtering of deleted records in lists
- Configurable via table metadata (`behaviors.soft_deletes`)
- Custom column support (default: `deleted_at`)

#### Integration
- `enableAuthentication()` method in DynamicCRUD
- `renderLoginForm()` and `renderRegistrationForm()` methods
- `handleAuthentication()` for login/register processing
- `isAuthenticated()` and `getCurrentUser()` methods
- `setCurrentUser()` for manual authentication integration
- Automatic permission enforcement in CRUD operations

#### Examples
- `examples/08-authentication/` - Complete authentication examples
  - `login.php` - Login form with test credentials
  - `register.php` - Registration form
  - `dashboard.php` - Protected page
  - `profile.php` - Edit profile with permissions
  - `setup.sql` - Database setup with test users
- `examples/07-rbac/` - RBAC examples
  - `basic-rbac.php` - Form with permission checks
  - `list-with-permissions.php` - List with dynamic buttons
  - `setup.sql` - Database setup with permissions
- `examples/09-soft-deletes/` - Soft deletes example
  - `index.php` - Complete soft delete demo with restore/force delete
  - `setup.sql` - Database setup with soft delete metadata

#### Testing
- **AuthenticationManagerTest** - 16 tests (100% passing)
  - Registration tests (5)
  - Login tests (4)
  - Session tests (3)
  - Security tests (4)
- **PermissionManagerTest** - 9 tests (100% passing)
- **TableMetadataTest** - 17 tests (100% passing)
- **SoftDeletesTest** - 10 tests (100% passing)
  - Soft delete behavior (2)
  - Restore functionality (1)
  - Force delete (2)
  - Metadata detection (3)
  - Multiple operations (2)
- All existing tests updated and passing (231/231)

#### Documentation
- Updated `docs/RBAC.md` with authentication guide
- Added authentication configuration examples
- Added rate limiting documentation
- Added session management guide

### 🐛 Fixed
- CSRF token being inserted into database during registration
- Action field being inserted into database during registration
- Password hashes not matching in test data
- ListGenerator not including owner_field in SELECT queries
- Permission checks failing due to missing owner data

### 🔧 Changed
- `docs/RBAC.md` renamed to "RBAC & Authentication Guide"
- Enhanced metadata support for authentication configuration
- Improved error messages for authentication failures

### 📊 Statistics
- **2 new classes** (AuthenticationManager, enhanced PermissionManager)
- **5 new examples** (4 in 08-authentication/, 1 in 09-soft-deletes/)
- **52 new tests** (16 auth + 9 permissions + 17 metadata + 10 soft deletes)
- **231 total tests** (100% passing, 90% coverage)
- **Authentication features**: 8 (register, login, logout, rate limiting, sessions, CSRF, remember me, auto-login)
- **RBAC features**: 6 (table permissions, row-level security, wildcards, owner checks, dynamic buttons, auto-enforcement)
- **Soft delete features**: 3 (soft delete, restore, force delete)

---

## [2.0.0] - 2024-12-XX

### 🎉 Major Release - Table Metadata System

This release introduces **table-level metadata** - a revolutionary way to configure CRUD behavior via JSON in database table comments. Zero PHP configuration needed!

### ✨ Added

#### Table Metadata (Phase 1 - Quick Wins)

**UI/UX Customization**
- Custom display names, icons, and colors per table
- Configurable list views (columns, sorting, pagination)
- Card vs table layout support
- `ListGenerator` class for rendering customizable lists
- Search functionality across multiple fields
- Pagination with query parameter preservation

**Dynamic Forms**
- Tabbed form layouts via table metadata
- Organized field groups with visual tab navigation
- JavaScript tab switching
- Automatic fallback to standard forms

**Automatic Behaviors**
- Auto-timestamps (`created_at`, `updated_at`)
- Auto-slug generation from source field
- Unique slug handling with numeric suffixes
- Zero-configuration via table metadata

**Search & Filters**
- Full-text search across multiple fields
- Select filters (dropdown)
- Date range filters
- Combined search + filters with AND/OR logic
- Query parameter preservation across pagination

#### Core Enhancements
- `TableMetadata` class with 20+ methods for metadata access
- Enhanced `CRUDHandler` with `applyAutomaticBehaviors()`
- Enhanced `ListGenerator` with search/filter rendering
- CSS styles in `FormGenerator` for professional UI
- `renderList()` method in `DynamicCRUD` class

#### Examples
- `06-table-metadata/ui-customization.php` - List views demo
- `06-table-metadata/dynamic-forms.php` - Tabbed forms demo
- `06-table-metadata/automatic-behaviors.php` - Auto-slug/timestamps demo
- `06-table-metadata/search-filters.php` - Search and filters demo
- Updated `index.html` with new v2.0 section

#### Documentation
- `docs/TABLE_METADATA.md` - Complete table metadata guide
- `docs/TABLE_METADATA_IDEAS.md` - Roadmap and future features
- `examples/06-table-metadata/README.md` - Examples guide

### 🐛 Fixed
- TypeError in `applyAutomaticBehaviors()` when `$isUpdate` was null
- Search not working due to missing `$_GET` parameter reading
- Pagination not preserving search and filter parameters
- Corrupted INSERT statements in `mysql.sql`
- Duplicate ALTER TABLE statements in setup scripts

### 🔧 Changed
- `ListGenerator` constructor now accepts optional `$schema` parameter
- `ListGenerator::render()` now reads from `$_GET` automatically
- Forms now include embedded CSS for better out-of-box experience
- Posts table now has 5 sample records for better demo

### 📊 Statistics
- **4 new examples** demonstrating v2.0 features
- **3 new classes** (TableMetadata, ListGenerator enhancements)
- **20+ new methods** across existing classes
- **100% Phase 1 completion** (all Quick Wins implemented)

---

## [1.5.0] - 2024-11-XX

### ✨ Added
- **Template System** - Blade-like syntax for custom layouts
- Layout inheritance (`@extends`, `@section`, `@yield`)
- Partials (`@include`)
- Automatic escaping (`{{ }}` vs `{!! !!}`)
- File caching for performance
- `BladeTemplate` class implementing `TemplateEngine` interface
- 17 new tests for templates (100% passing)

### 📚 Documentation
- `docs/TEMPLATES.md` - Complete template system guide

---

## [1.4.0] - 2024-11-XX

### ✨ Added
- **Internationalization (i18n)** - Multi-language support
- 3 languages included: English, Spanish, French
- Auto-detection from URL, session, or browser
- `Translator` class with locale management
- Client + Server translation support
- Advanced M:N UI with checkboxes and search
- 31 new tests for i18n (100% passing)

### 📚 Documentation
- `docs/I18N.md` - Complete i18n guide

---

## [1.3.0] - 2024-11-XX

### ✨ Added
- **PostgreSQL support** via Adapter pattern
- Auto-detection of database driver from PDO
- `PostgreSQLAdapter` class
- Docker setup for MySQL & PostgreSQL
- `docker-compose.yml` with 3 services

### 📚 Documentation
- `DOCKER_SETUP.md` - Docker setup guide

---

## [1.2.0] - 2024-11-XX

### ✨ Added
- **Virtual fields** - Password confirmation, terms acceptance
- `VirtualField` class with custom validators
- Comprehensive test suite (113 tests)
- CI/CD pipeline (GitHub Actions)
- FormGenerator enhancements (16+ metadata options)

### 📚 Documentation
- `docs/VIRTUAL_FIELDS.md` - Virtual fields guide

---

## [1.1.0] - 2024-11-XX

### ✨ Added
- Many-to-many relationships with multi-select UI
- Hooks/Events system (10 lifecycle hooks)
- Audit logging for change tracking
- File uploads with MIME validation
- Client + Server validation

### 📚 Documentation
- `docs/HOOKS.md` - Hooks system guide
- `docs/MANY_TO_MANY.md` - M:N relationships guide

---

## [1.0.0] - 2024-11-XX

### 🎉 Initial Release

### ✨ Features
- Automatic form generation from database schema
- Full CRUD operations (Create, Read, Update, Delete)
- Foreign key auto-detection with dropdown selects
- CSRF protection built-in
- SQL injection prevention with prepared statements
- XSS protection with automatic escaping
- Smart NULL handling for nullable fields
- ENUM field support with auto-generated selects
- MySQL 5.7+ support
- PSR-4 autoloading
- Zero configuration needed for basic CRUD

### 📚 Documentation
- `README.md` - Complete documentation
- `docs/CUSTOMIZATION.md` - Metadata options guide
- `CONTRIBUTING.md` - Contribution guidelines

### 🧪 Testing
- 195 automated tests
- 76% pass rate (149 passing, 40 failing, 6 skipped)
- PHPUnit 9.5+ support

---

## Version History Summary

| Version | Release Date | Key Features |
|---------|--------------|--------------|
| **2.3.0** | 2025-01-XX | Notifications & Webhooks |
| **2.2.0** | 2025-01-XX | Validation Rules & Business Logic |
| **2.1.0** | 2024-12-XX | Authentication & RBAC |
| **2.0.0** | 2024-12-XX | Table Metadata System (Phase 1) |
| 1.5.0 | 2024-11-XX | Template System |
| 1.4.0 | 2024-11-XX | Internationalization (i18n) |
| 1.3.0 | 2024-11-XX | PostgreSQL Support |
| 1.2.0 | 2024-11-XX | Virtual Fields |
| 1.1.0 | 2024-11-XX | M:N Relationships, Hooks, Audit |
| 1.0.0 | 2024-11-XX | Initial Release |

---

## Upgrade Guides

### Upgrading to 2.0.0

**Breaking Changes:** None! v2.0 is fully backward compatible.

**New Features:**
1. Add table metadata to your tables (optional):
```sql
ALTER TABLE users COMMENT = '{"display_name": "Users", "icon": "👥"}';
```

2. Clear cache:
```bash
php examples/clear_cache.php
```

3. Use new features:
```php
$crud->renderList();  // Now uses metadata
```

**Migration Time:** < 5 minutes per table

---

## Roadmap

### Phase 2: Security & Validation (v2.1-v2.3) - ✅ COMPLETED
- ✅ Permissions & Security (RBAC, row-level security)
- ✅ Authentication (register, login, rate limiting)
- ✅ Soft deletes
- ✅ Validation & Rules (unique_together, conditional, business rules)
- ✅ Notifications (email, webhooks)

### Phase 3: Advanced Features (v2.4) - Planned
- Versioning & Audit enhancements
- Advanced Relationships (polymorphic, has-many-through)
- API & Export (REST API, CSV/Excel/PDF)

### Phase 4: Analytics (v2.5) - Planned
- Analytics & Reporting (metrics, charts, dashboards)

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
