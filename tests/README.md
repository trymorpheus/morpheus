# Morpheus Test Suite

## Overview

Comprehensive PHPUnit test suite for DynamicCRUD library covering core functionality.

## Test Coverage

### ✅ ValidationEngine (7/7 tests - 100%)
- Required fields validation
- Email format validation
- URL format validation
- Min/max numeric validation
- Minlength validation
- Nullable fields handling
- Valid data acceptance

### ✅ FormGenerator (22/22 tests - 100%)
- Basic form rendering
- CSRF token inclusion
- Text/email/number inputs
- Required attributes
- Existing data population
- Hidden ID fields
- Submit buttons
- Labels and custom labels
- Tooltips
- Min/max/minlength attributes
- Textarea rendering
- ENUM select rendering
- Hidden fields
- File inputs with accept attribute
- Multipart form encoding
- Asset inclusion (CSS/JS)
- HTML escaping

### ✅ SchemaAnalyzer (7/8 tests - 87.5%)
- Table schema retrieval
- Primary key detection
- Column structure parsing
- Metadata extraction from comments
- Foreign key detection
- Cache integration
- Invalid table handling
- ⏭️ ENUM values (skipped - TEMPORARY table limitation)

### ✅ SecurityModule (6/6 tests - 100%)
- Input sanitization
- Empty to NULL conversion
- Whitespace trimming
- Column filtering
- HTML output escaping

### ⚠️ FileUploadHandler (4/8 tests - 50%)
- Directory creation
- No file handling
- Upload error detection
- File size validation
- ⏭️ Unique filename generation (skipped - requires HTTP upload)
- ⏭️ Extension preservation (skipped - requires HTTP upload)
- ⏭️ Relative path creation (skipped - requires HTTP upload)
- ⏭️ Permission validation (skipped - Windows incompatible)

## Running Tests

### All Tests
```bash
vendor/bin/phpunit
```

### With Detailed Output
```bash
vendor/bin/phpunit --testdox
```

### Specific Test Class
```bash
vendor/bin/phpunit tests/ValidationEngineTest.php
```

### With Coverage (requires Xdebug)
```bash
vendor/bin/phpunit --coverage-html coverage/
```

## Configuration

Tests are configured via `phpunit.xml`:
- Database: `test` (MySQL)
- Host: `localhost`
- User: `root`
- Password: `rootpassword`

Update environment variables in `phpunit.xml` to match your setup.

## Test Statistics

- **Total Tests**: 113
- **Passing**: 108 (95.6%)
- **Skipped**: 5 (4.4%)
- **Failed**: 0 (0%)
- **Assertions**: 239+

## Skipped Tests

Some tests are skipped due to technical limitations:

1. **File Upload Tests**: `move_uploaded_file()` only works with actual HTTP uploads, not unit tests
2. **ENUM Extraction**: TEMPORARY tables not visible in INFORMATION_SCHEMA
3. **Permission Tests**: Windows doesn't support Unix-style chmod

These features are tested manually via examples.

## Database Requirements

Tests require a MySQL database with the following tables:
- `users` (from `examples/setup.sql`)
- `posts` (from `examples/setup_phase2.sql`)

Run setup scripts before testing:
```bash
mysql -u root -p test < examples/setup.sql
mysql -u root -p test < examples/setup_phase2.sql
```

### ✅ CRUDHandler (20/20 tests - 100%)
- Constructor and initialization
- All 10 lifecycle hooks (before/after Validate, Save, Create, Update, Delete)
- Create/Update/Delete operations
- Transaction rollback on error
- CSRF token validation
- Validation failure handling
- Fluent interface
- Many-to-many relationship configuration
- Audit logging enablement

### ✅ AuditLogger (6/6 tests - 100%)
- Log create operations
- Log update operations with old/new values
- Log delete operations
- User ID tracking
- History retrieval (multiple entries, chronological order)
- Empty history for non-existent records

### ✅ ListGenerator (13/13 tests - 100%)
- Default pagination (20 per page)
- Custom per-page limits
- Page navigation (first, middle, last)
- Filtering by column values
- Sorting (ASC/DESC)
- Table HTML rendering
- Empty state handling
- Pagination controls rendering

### ✅ FileCacheStrategy (9/9 tests - 100%)
- Directory creation
- Set and get operations
- Non-existent key handling
- TTL expiration
- Cache invalidation
- Clear all cache
- Multiple keys management
- Overwrite existing entries

### ✅ DynamicCRUD (14/14 tests - 100%) - Integration Tests
- Constructor with/without cache
- Form rendering (create/edit)
- Complete CRUD workflows (create, update, delete)
- List operations with pagination
- Hooks integration
- Fluent interface chaining
- Audit logging integration
- Many-to-many configuration
- Validation failures
- CSRF protection

## Future Test Coverage

Planned for future releases:
- Additional cache strategies (Redis, Memcached)
