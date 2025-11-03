# RBAC (Role-Based Access Control) Guide

**DynamicCRUD v2.1+**

## Overview

DynamicCRUD includes a powerful RBAC system that allows you to control access to CRUD operations based on user roles. The system supports both **table-level permissions** (who can perform actions on a table) and **row-level security** (users can only access their own records).

## Key Features

✅ **Table-Level Permissions** - Control create, read, update, delete by role  
✅ **Row-Level Security** - Users can only edit/delete their own records  
✅ **Zero Configuration** - Define permissions in table metadata  
✅ **Automatic Enforcement** - Permissions checked in forms, lists, and handlers  
✅ **Flexible Roles** - Support any role names (admin, editor, author, etc.)  
✅ **Wildcard Support** - Use `*` for public access  

---

## Quick Start

### 1. Define Permissions in Table Metadata

```sql
CREATE TABLE posts (
    id INT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255),
    content TEXT
) COMMENT = '{
    "permissions": {
        "create": ["admin", "editor"],
        "read": ["*"],
        "update": ["admin", "editor"],
        "delete": ["admin"]
    },
    "row_level_security": {
        "enabled": true,
        "owner_field": "user_id",
        "owner_can_edit": true,
        "owner_can_delete": false
    }
}';
```

### 2. Set Current User in Your Application

```php
$crud = new DynamicCRUD($pdo, 'posts');

// Set current user (from session, JWT, etc.)
$crud->setCurrentUser($userId, $userRole);

// Permissions are now automatically enforced
echo $crud->renderForm();
echo $crud->renderList();
$result = $crud->handleSubmission();
```

---

## Table-Level Permissions

Control who can perform CRUD operations on a table.

### Configuration

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

### Permission Actions

| Action | Description | When Checked |
|--------|-------------|--------------|
| `create` | Who can create new records | Before showing create form, before INSERT |
| `read` | Who can view records | Before showing list/form, before SELECT |
| `update` | Who can edit records | Before showing edit form, before UPDATE |
| `delete` | Who can delete records | Before showing delete button, before DELETE |

### Special Values

- **`["*"]`** - Everyone (including guests)
- **`[]`** - No one (disabled)
- **`["admin", "editor"]`** - Only these roles

### Examples

#### Public Read, Admin Write
```json
{
  "permissions": {
    "create": ["admin"],
    "read": ["*"],
    "update": ["admin"],
    "delete": ["admin"]
  }
}
```

#### Multi-Role Collaboration
```json
{
  "permissions": {
    "create": ["admin", "editor", "author"],
    "read": ["admin", "editor", "author"],
    "update": ["admin", "editor"],
    "delete": ["admin"]
  }
}
```

#### Locked Table (Admin Only)
```json
{
  "permissions": {
    "create": ["admin"],
    "read": ["admin"],
    "update": ["admin"],
    "delete": ["admin"]
  }
}
```

---

## Row-Level Security (RLS)

Allow users to only access their own records.

### Configuration

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

### Options

| Option | Type | Description | Default |
|--------|------|-------------|---------|
| `enabled` | boolean | Enable row-level security | `false` |
| `owner_field` | string | Column that stores owner ID | `"user_id"` |
| `owner_can_edit` | boolean | Owners can edit their records | `true` |
| `owner_can_delete` | boolean | Owners can delete their records | `false` |

### How It Works

1. **Table-level check first**: User must have table-level permission
2. **Row-level check second**: If record has owner, check if user is owner
3. **Owner permissions**: Apply `owner_can_edit` and `owner_can_delete` rules

### Example: Blog Posts

```json
{
  "permissions": {
    "create": ["admin", "editor", "author"],
    "read": ["*"],
    "update": ["admin", "editor"],
    "delete": ["admin"]
  },
  "row_level_security": {
    "enabled": true,
    "owner_field": "user_id",
    "owner_can_edit": true,
    "owner_can_delete": false
  }
}
```

**Behavior:**
- **Admin**: Can edit/delete all posts
- **Editor**: Can edit all posts, delete none
- **Author**: Can edit only their own posts, delete none
- **User**: Can only read

---

## PHP API

### Setting Current User

```php
// Method 1: Via DynamicCRUD
$crud = new DynamicCRUD($pdo, 'posts');
$crud->setCurrentUser($userId, $role);

// Method 2: Via PermissionManager
$pm = $crud->getPermissionManager();
$pm->setCurrentUser($userId, $role);
```

### Checking Permissions

```php
$pm = $crud->getPermissionManager();

// Check specific actions
if ($pm->canCreate()) {
    echo $crud->renderForm();
}

if ($pm->canUpdate($record)) {
    echo '<a href="?edit=' . $record['id'] . '">Edit</a>';
}

if ($pm->canDelete($record)) {
    echo '<a href="?delete=' . $record['id'] . '">Delete</a>';
}

// Generic check
if ($pm->can('update', $record)) {
    // Allow update
}
```

### Getting Current User Info

```php
$pm = $crud->getPermissionManager();

$userId = $pm->getCurrentUserId();    // int|null
$role = $pm->getCurrentUserRole();    // string (default: 'guest')
```

### Automatic Enforcement

Permissions are automatically checked in:

```php
// Forms - renderForm() checks canCreate/canUpdate
echo $crud->renderForm($id);

// Lists - renderList() hides edit/delete buttons based on permissions
echo $crud->renderList();

// Submissions - handleSubmission() validates permissions
$result = $crud->handleSubmission();
// Returns error if no permission

// Deletes - delete() validates permissions
$crud->delete($id);
// Throws exception if no permission
```

---

## Integration with Authentication

### Session-Based Auth

```php
session_start();

// After login
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_role'] = $user['role'];

// In CRUD pages
$crud = new DynamicCRUD($pdo, 'posts');
$crud->setCurrentUser(
    $_SESSION['user_id'] ?? null,
    $_SESSION['user_role'] ?? 'guest'
);
```

### JWT-Based Auth

```php
$jwt = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$payload = JWT::decode($jwt, $secret);

$crud = new DynamicCRUD($pdo, 'posts');
$crud->setCurrentUser($payload->user_id, $payload->role);
```

### Custom Auth

```php
class Auth {
    public static function user() {
        // Your auth logic
        return ['id' => 1, 'role' => 'admin'];
    }
}

$user = Auth::user();
$crud = new DynamicCRUD($pdo, 'posts');
$crud->setCurrentUser($user['id'], $user['role']);
```

---

## Common Patterns

### Pattern 1: Blog System

```sql
-- Posts: Authors can create/edit own, editors can edit all
CREATE TABLE posts (
    id INT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255),
    content TEXT,
    status ENUM('draft', 'published')
) COMMENT = '{
    "permissions": {
        "create": ["admin", "editor", "author"],
        "read": ["*"],
        "update": ["admin", "editor"],
        "delete": ["admin"]
    },
    "row_level_security": {
        "enabled": true,
        "owner_field": "user_id",
        "owner_can_edit": true,
        "owner_can_delete": false
    }
}';

-- Comments: Users can edit/delete own comments
CREATE TABLE comments (
    id INT PRIMARY KEY,
    post_id INT,
    user_id INT NOT NULL,
    content TEXT
) COMMENT = '{
    "permissions": {
        "create": ["admin", "editor", "author", "user"],
        "read": ["*"],
        "update": ["admin", "editor"],
        "delete": ["admin", "editor"]
    },
    "row_level_security": {
        "enabled": true,
        "owner_field": "user_id",
        "owner_can_edit": true,
        "owner_can_delete": true
    }
}';
```

### Pattern 2: Admin Panel

```sql
-- Users: Only admins can manage
CREATE TABLE users (
    id INT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(255),
    role ENUM('admin', 'user')
) COMMENT = '{
    "permissions": {
        "create": ["admin"],
        "read": ["admin"],
        "update": ["admin"],
        "delete": ["admin"]
    }
}';

-- Settings: Only admins can modify
CREATE TABLE settings (
    id INT PRIMARY KEY,
    key VARCHAR(100),
    value TEXT
) COMMENT = '{
    "permissions": {
        "create": ["admin"],
        "read": ["admin", "editor"],
        "update": ["admin"],
        "delete": ["admin"]
    }
}';
```

### Pattern 3: Multi-Tenant SaaS

```sql
-- Projects: Users can only see their company's projects
CREATE TABLE projects (
    id INT PRIMARY KEY,
    company_id INT NOT NULL,
    name VARCHAR(255)
) COMMENT = '{
    "permissions": {
        "create": ["admin", "manager"],
        "read": ["admin", "manager", "user"],
        "update": ["admin", "manager"],
        "delete": ["admin"]
    },
    "row_level_security": {
        "enabled": true,
        "owner_field": "company_id",
        "owner_can_edit": false,
        "owner_can_delete": false
    }
}';
```

---

## Error Handling

### Permission Denied Errors

```php
$result = $crud->handleSubmission();

if (!$result['success']) {
    if (strpos($result['error'], 'permiso') !== false) {
        // Permission denied
        http_response_code(403);
        echo "Access Denied";
    }
}
```

### Custom Error Messages

```php
// Use translator for custom messages
$crud->setLocale('es');

// Error messages are automatically translated
// "No tienes permiso para realizar esta acción"
```

---

## Advanced Usage

### Dynamic Roles

```php
// Roles can be determined dynamically
$userRole = $user['is_premium'] ? 'premium' : 'free';
$crud->setCurrentUser($user['id'], $userRole);
```

### Multiple Permission Checks

```php
$pm = $crud->getPermissionManager();

// Check multiple permissions
$canManage = $pm->canUpdate($record) && $pm->canDelete($record);

if ($canManage) {
    echo '<div class="admin-tools">...</div>';
}
```

### Filtering Records by Permission

```php
// Get all records user can read
$records = $crud->list();

// PermissionManager automatically filters by row-level security
$pm = $crud->getPermissionManager();
$filtered = $pm->filterRecordsByPermission($records);
```

---

## Best Practices

### 1. Always Set Current User

```php
// ❌ Bad: Permissions not enforced
$crud = new DynamicCRUD($pdo, 'posts');
echo $crud->renderForm();

// ✅ Good: Permissions enforced
$crud = new DynamicCRUD($pdo, 'posts');
$crud->setCurrentUser($userId, $role);
echo $crud->renderForm();
```

### 2. Use Consistent Role Names

```php
// ✅ Good: Standard role names
'admin', 'editor', 'author', 'user', 'guest'

// ❌ Bad: Inconsistent naming
'Administrator', 'EDITOR', 'writer', 'normal_user'
```

### 3. Principle of Least Privilege

```json
{
  "permissions": {
    "create": ["admin"],
    "read": ["*"],
    "update": ["admin"],
    "delete": ["admin"]
  }
}
```

Start restrictive, open up as needed.

### 4. Test All Roles

```php
// Test with different roles
foreach (['admin', 'editor', 'author', 'user', 'guest'] as $role) {
    $crud->setCurrentUser(1, $role);
    // Test CRUD operations
}
```

### 5. Log Permission Denials

```php
$result = $crud->handleSubmission();

if (!$result['success'] && strpos($result['error'], 'permiso') !== false) {
    error_log("Permission denied: User {$userId} ({$role}) tried to access {$table}");
}
```

---

## Troubleshooting

### Permissions Not Working

**Problem**: Permissions are not being enforced

**Solution**: Make sure you're calling `setCurrentUser()`:
```php
$crud->setCurrentUser($userId, $role);
```

### Always Getting "Permission Denied"

**Problem**: All operations return permission denied

**Solution**: Check your role name matches metadata:
```php
// Metadata has: "create": ["admin"]
// But you're setting: $crud->setCurrentUser(1, "administrator")
// Fix: Use "admin" instead
```

### Row-Level Security Not Working

**Problem**: Users can see all records, not just their own

**Solution**: Ensure `owner_field` matches your column:
```json
{
  "row_level_security": {
    "enabled": true,
    "owner_field": "user_id"  // Must match actual column name
  }
}
```

### Buttons Still Showing

**Problem**: Edit/delete buttons show even without permission

**Solution**: Make sure you're using `renderList()`, not custom HTML:
```php
// ✅ Good: Buttons filtered automatically
echo $crud->renderList();

// ❌ Bad: Custom HTML doesn't check permissions
echo '<a href="?edit=' . $id . '">Edit</a>';
```

---

## Examples

See `examples/07-rbac/` for working examples:

- **basic-rbac.php** - Form with permission checks
- **list-with-permissions.php** - List with dynamic action buttons
- **setup.sql** - Database setup with permissions

---

## Migration from v1.x

If you're upgrading from DynamicCRUD v1.x:

```php
// v1.x (no RBAC)
$crud = new DynamicCRUD($pdo, 'posts');
echo $crud->renderForm();

// v2.1+ (with RBAC)
$crud = new DynamicCRUD($pdo, 'posts');
$crud->setCurrentUser($userId, $role);  // Add this line
echo $crud->renderForm();
```

Tables without permission metadata work as before (no restrictions).

---

## Related Documentation

- [Table Metadata Guide](TABLE_METADATA.md) - Complete metadata options
- [Hooks System](HOOKS.md) - Custom logic with hooks
- [Audit Logging](../README.md#audit-logging) - Track who changed what

---

**Made with ❤️ by Mario Raúl Carbonell Martínez**
