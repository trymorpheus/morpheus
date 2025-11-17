# 06. Multi-Database Support

DynamicCRUD supports multiple databases via the Adapter pattern.

## Supported Databases

- **MySQL 5.7+** - Full support
- **PostgreSQL 12+** - Full support
- **SQL Server** - Planned
- **SQLite** - Planned

## Auto-Detection

DynamicCRUD automatically detects your database driver from the PDO connection:

```php
// MySQL
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'pass');
$crud = new Morpheus($pdo, 'users'); // Uses MySQLAdapter

// PostgreSQL
$pdo = new PDO('pgsql:host=localhost;dbname=test', 'postgres', 'pass');
$crud = new Morpheus($pdo, 'users'); // Uses PostgreSQLAdapter
```

## Database-Specific Features

### MySQL
- ENUM type support
- AUTO_INCREMENT primary keys
- JSON column comments for metadata
- Foreign key constraints

### PostgreSQL
- SERIAL primary keys
- CHECK constraints for enums
- JSONB for audit logs
- Advanced indexing

## Type Normalization

Adapters normalize database-specific types to common types:

**MySQL:**
- `varchar`, `character varying` → `varchar`
- `int`, `integer`, `int4` → `int`
- `bigint`, `int8` → `bigint`

**PostgreSQL:**
- `character varying` → `varchar`
- `integer`, `int4` → `int`
- `bigint`, `int8` → `bigint`

## Extending Support

To add a new database, implement the `DatabaseAdapter` interface:

```php
interface DatabaseAdapter {
    public function getTableSchema(string $table): array;
    public function getForeignKeys(string $table): array;
    public function getEnumValues(string $table, string $column): array;
    public function quote(string $identifier): string;
}
```

## Examples

See `mysql.php` and `postgresql.php` for database-specific examples.

## Setup

Use the SQL scripts in `../setup/`:
- `mysql.sql` - MySQL setup
- `postgresql.sql` - PostgreSQL setup
