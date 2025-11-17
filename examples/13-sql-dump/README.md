# SQL Dump & Import Examples

Export and import table structure and data with metadata preserved.

## Features

- **Full SQL Dump** - Structure + data + metadata
- **Structure Only** - Table definition with comments
- **Data Only** - INSERT statements
- **Safe Import** - Transaction support with confirmation
- **CLI Commands** - Easy command-line usage

## CLI Usage

### Export SQL Dump

```bash
# Full dump (structure + data)
php bin/morpheus dump:sql users --output=users.sql

# Structure only
php bin/morpheus dump:sql users --output=structure.sql --structure-only

# Data only
php bin/morpheus dump:sql users --output=data.sql --data-only

# Output to console
php bin/morpheus dump:sql users
```

### Import SQL Dump

```bash
# Import with confirmation
php bin/morpheus import:sql backup.sql

# Import without confirmation (force)
php bin/morpheus import:sql backup.sql --force
```

## What's Included

The SQL dump includes:
- ✅ Table structure (CREATE TABLE)
- ✅ Column definitions with types
- ✅ Primary keys and indexes
- ✅ Foreign key constraints
- ✅ **Table metadata** (COMMENT with JSON)
- ✅ **Column metadata** (COMMENT with JSON)
- ✅ All data (INSERT statements)

## Use Cases

1. **Backup & Restore** - Full database backup with metadata
2. **Migration** - Move tables between environments
3. **Version Control** - Track schema changes in Git
4. **Testing** - Create test fixtures with data
5. **Documentation** - Share table structure with team

## Example Output

```sql
-- DynamicCRUD SQL Dump
-- Table: users
-- Generated: 2024-01-15 10:30:00

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL COMMENT '{"type": "email", "label": "Email Address"}',
  `role` varchar(50) DEFAULT 'user',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='{"display_name": "Users", "icon": "👤"}';

-- Data for table `users`

INSERT INTO `users` (`id`, `name`, `email`, `role`) VALUES (1, 'John Doe', 'john@example.com', 'admin');
INSERT INTO `users` (`id`, `name`, `email`, `role`) VALUES (2, 'Jane Smith', 'jane@example.com', 'user');

SET FOREIGN_KEY_CHECKS=1;
```

## Notes

- DDL statements (CREATE, DROP, ALTER) are executed outside transactions
- DML statements (INSERT, UPDATE, DELETE) are executed in transactions
- Import includes confirmation prompt (use --force to skip)
- Metadata is preserved in table and column comments
