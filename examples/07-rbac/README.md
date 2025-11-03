# RBAC (Role-Based Access Control) Examples

This directory contains examples demonstrating DynamicCRUD's permission system.

## Setup

1. Run the SQL setup:
```bash
mysql -u root -p test < setup.sql
```

2. Start PHP server:
```bash
php -S localhost:8000
```

3. Open: http://localhost:8000/basic-rbac.php

## Features Demonstrated

### Table-Level Permissions
Configure who can perform CRUD operations:
```json
{
  "permissions": {
    "create": ["admin", "editor", "author"],
    "read": ["*"],
    "update": ["admin", "editor"],
    "delete": ["admin"]
  }
}
```

### Row-Level Security
Users can only edit their own records:
```json
{
  "row_level_security": {
    "enabled": true,
    "owner_field": "user_id",
    "owner_can_edit": true,
    "owner_can_delete": false
  }
}
```

## Roles

- **admin**: Full access to everything
- **editor**: Can create/edit posts, moderate comments
- **author**: Can create/edit own posts only
- **user**: Can read and comment
- **guest**: Read-only access

## Testing

Change `$currentUserRole` in `basic-rbac.php` to test different permissions:

```php
$currentUserRole = 'admin';   // Full access
$currentUserRole = 'editor';  // No delete
$currentUserRole = 'author';  // Own posts only
$currentUserRole = 'user';    // Read only
$currentUserRole = 'guest';   // No access
```

## API Usage

```php
// Set current user
$crud->setCurrentUser($userId, $role);

// Check permissions
$pm = $crud->getPermissionManager();
if ($pm->canCreate()) {
    // Show create form
}

if ($pm->canUpdate($record)) {
    // Show edit button
}

// Permissions are automatically checked in handleSubmission()
$result = $crud->handleSubmission();
// Returns error if no permission
```

## Files

- `basic-rbac.php` - Basic RBAC example (form permissions)
- `list-with-permissions.php` - List with permission-based action buttons
- `setup.sql` - Database setup with permissions
- `README.md` - This file
