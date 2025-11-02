# DynamicCRUD - Project Structure

## Directory Organization

```
dynamicCRUD/
├── src/                          # Core library source code
│   ├── Cache/                    # Caching subsystem
│   │   ├── CacheStrategy.php     # Cache interface
│   │   └── FileCacheStrategy.php # File-based cache implementation
│   ├── DynamicCRUD.php           # Main public API class
│   ├── CRUDHandler.php           # CRUD operations, hooks, transactions
│   ├── SchemaAnalyzer.php        # Database schema introspection
│   ├── FormGenerator.php         # HTML form generation
│   ├── ValidationEngine.php      # Server-side validation
│   ├── SecurityModule.php        # CSRF, sanitization, security
│   ├── ListGenerator.php         # List views with pagination
│   ├── FileUploadHandler.php     # File upload processing
│   └── AuditLogger.php           # Change tracking system
├── examples/                     # Working demonstrations
│   ├── assets/                   # Client-side resources
│   │   ├── dynamiccrud.css       # Styling
│   │   └── dynamiccrud.js        # Client validation
│   ├── uploads/                  # File upload destination
│   ├── *.php                     # 8 example files
│   └── setup*.sql                # Database setup scripts
├── docs/                         # User documentation
│   ├── HOOKS.md                  # Hooks system guide
│   ├── MANY_TO_MANY.md           # M:N relationships guide
│   └── CUSTOMIZATION.md          # Metadata customization guide
├── tests/                        # Unit tests
│   ├── SchemaAnalyzerTest.php
│   └── ValidationEngineTest.php
├── cache/                        # Schema cache storage
├── .github/                      # GitHub templates
│   └── ISSUE_TEMPLATE/
├── local_docs/                   # Internal development docs
├── composer.json                 # Package definition
├── README.md                     # Main documentation
└── LICENSE                       # MIT license
```

## Core Components

### DynamicCRUD (Main API)
**File**: `src/DynamicCRUD.php`
**Purpose**: Public-facing API and orchestration layer
**Responsibilities**:
- Instantiates and coordinates all subsystems
- Provides simple methods: renderForm(), handleSubmission(), renderList()
- Manages configuration and options
- Delegates to specialized components

### CRUDHandler
**File**: `src/CRUDHandler.php`
**Purpose**: CRUD operation execution and lifecycle management
**Responsibilities**:
- Executes INSERT, UPDATE, DELETE operations
- Manages database transactions
- Implements hooks/events system (10 hooks)
- Handles many-to-many relationship synchronization
- Coordinates with AuditLogger for change tracking

### SchemaAnalyzer
**File**: `src/SchemaAnalyzer.php`
**Purpose**: Database schema introspection
**Responsibilities**:
- Queries INFORMATION_SCHEMA for table structure
- Detects foreign key relationships
- Parses JSON metadata from column comments
- Identifies primary keys and constraints
- Caches schema information for performance

### FormGenerator
**File**: `src/FormGenerator.php`
**Purpose**: HTML form rendering
**Responsibilities**:
- Generates form HTML from schema metadata
- Creates appropriate input types for each field
- Renders foreign key dropdowns
- Generates many-to-many multi-selects
- Includes CSRF tokens
- Adds accessibility attributes (ARIA labels)

### ValidationEngine
**File**: `src/ValidationEngine.php`
**Purpose**: Server-side data validation
**Responsibilities**:
- Type validation (email, URL, number formats)
- Length validation (min/max, minlength)
- Required field checking
- ENUM value validation
- Foreign key existence validation
- Custom validation via metadata

### SecurityModule
**File**: `src/SecurityModule.php`
**Purpose**: Security features
**Responsibilities**:
- CSRF token generation and validation
- Input sanitization (XSS prevention)
- Session management for tokens
- Security header recommendations

### FileUploadHandler
**File**: `src/FileUploadHandler.php`
**Purpose**: File upload processing
**Responsibilities**:
- Real MIME type validation using finfo
- File size validation
- Unique filename generation
- File movement to upload directory
- Error handling for upload failures

### ListGenerator
**File**: `src/ListGenerator.php`
**Purpose**: Data listing and pagination
**Responsibilities**:
- Generates paginated record lists
- Provides edit/delete action links
- Handles sorting and filtering
- Renders navigation controls

### AuditLogger
**File**: `src/AuditLogger.php`
**Purpose**: Change tracking
**Responsibilities**:
- Logs all create/update/delete operations
- Records user ID, IP address, timestamp
- Stores old and new values as JSON
- Maintains audit trail table

### Cache System
**Files**: `src/Cache/CacheStrategy.php`, `src/Cache/FileCacheStrategy.php`
**Purpose**: Performance optimization
**Responsibilities**:
- Caches database schema metadata
- Implements strategy pattern for different cache backends
- File-based implementation included
- Reduces repeated INFORMATION_SCHEMA queries

## Architectural Patterns

### Strategy Pattern
Used in caching system - CacheStrategy interface allows different cache implementations (file, Redis, Memcached) without changing core code.

### Hook/Event Pattern
10 lifecycle hooks allow extending functionality without modifying library code. Hooks receive data and can modify or cancel operations.

### Single Responsibility Principle
Each class has one clear purpose - schema analysis, form generation, validation, etc. This makes the codebase maintainable and testable.

### Dependency Injection
PDO connection injected into DynamicCRUD constructor, allowing different database connections and easier testing.

### Transaction Management
All write operations wrapped in database transactions with automatic rollback on errors, ensuring data integrity.

## Data Flow

### Form Rendering Flow
1. User requests form → DynamicCRUD::renderForm()
2. SchemaAnalyzer retrieves/caches table structure
3. SecurityModule generates CSRF token
4. FormGenerator creates HTML with metadata
5. HTML returned to user

### Form Submission Flow
1. POST data → DynamicCRUD::handleSubmission()
2. SecurityModule validates CSRF token
3. SecurityModule sanitizes input data
4. Hook: beforeValidate (modify data)
5. ValidationEngine validates all fields
6. Hook: afterValidate (cross-field validation)
7. Hook: beforeSave (common pre-save logic)
8. Hook: beforeCreate OR beforeUpdate (specific logic)
9. Transaction BEGIN
10. CRUDHandler executes INSERT/UPDATE
11. AuditLogger records change (if enabled)
12. Many-to-many sync (if configured)
13. Hook: afterCreate OR afterUpdate
14. Hook: afterSave
15. Transaction COMMIT
16. Return success with record ID

### Delete Flow
1. Delete request → DynamicCRUD::handleSubmission()
2. Hook: beforeDelete (check dependencies)
3. Transaction BEGIN
4. CRUDHandler executes DELETE
5. AuditLogger records deletion
6. Hook: afterDelete (cleanup files)
7. Transaction COMMIT

## Configuration Files

### composer.json
Defines package metadata, dependencies, and autoloading:
- PHP 8.0+ requirement
- PDO, fileinfo, JSON extensions required
- PSR-4 autoloading for DynamicCRUD namespace
- PHPUnit for testing

### Database Setup Scripts
Located in `examples/`:
- `setup.sql`: Basic tables (users, categories, posts)
- `setup_phase2.sql`: Foreign key relationships
- `setup_phase3.sql`: File upload fields
- `setup_phase4.sql`: ENUM fields
- `setup_many_to_many.sql`: M:N pivot tables
- `setup_audit.sql`: Audit logging table

## Example Files

### Basic Examples
- `index.php`: Simple user CRUD
- `categories.php`: Complete CRUD with delete
- `posts.php`: Foreign key relationships

### Advanced Examples
- `products.php`: File uploads
- `contacts.php`: Client validation and UX
- `hooks_demo.php`: All 10 hooks demonstrated
- `many_to_many_demo.php`: M:N relationships
- `audit_demo.php`: Change tracking

### Utility Examples
- `clear_cache.php`: Cache management
- `debug_csrf.php`: CSRF token debugging
- `validation_demo.php`: Validation testing

## Namespace Structure

```
DynamicCRUD\
├── DynamicCRUD           # Main class
├── CRUDHandler
├── SchemaAnalyzer
├── FormGenerator
├── ValidationEngine
├── SecurityModule
├── ListGenerator
├── FileUploadHandler
├── AuditLogger
└── Cache\
    ├── CacheStrategy     # Interface
    └── FileCacheStrategy # Implementation
```

PSR-4 autoloading maps `DynamicCRUD\` namespace to `src/` directory.
