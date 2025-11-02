# DynamicCRUD Test Suite

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

- **Total Tests**: 29
- **Passing**: 24 (82.8%)
- **Skipped**: 5 (17.2%)
- **Failed**: 0 (0%)
- **Assertions**: 55+

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

## Future Test Coverage

Planned for future releases:
- CRUDHandler (hooks, transactions, M:N)
- FormGenerator (HTML generation)
- AuditLogger (change tracking)
- DynamicCRUD (integration tests)
- Cache strategies
