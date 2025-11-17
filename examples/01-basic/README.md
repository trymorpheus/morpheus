# 01. Basic CRUD

The simplest possible CRUD implementation with DynamicCRUD.

## What You'll Learn

- ✅ Connect to database with PDO
- ✅ Create a CRUD instance in 1 line
- ✅ Handle form submissions automatically
- ✅ Render forms with zero configuration

## The Code

```php
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'pass');
$crud = new Morpheus($pdo, 'users');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
}

echo $crud->renderForm($_GET['id'] ?? null);
```

That's it! **3 lines of code** for a complete CRUD system.

## What Happens Automatically

1. **Schema Analysis** - DynamicCRUD reads your table structure
2. **Form Generation** - Creates appropriate inputs for each field type
3. **Validation** - Server + client-side validation based on constraints
4. **CSRF Protection** - Automatic token generation and validation
5. **SQL Injection Prevention** - All queries use prepared statements
6. **NULL Handling** - Respects nullable fields

## Run the Example

```bash
# Start PHP server
php -S localhost:8000

# Open in browser
http://localhost:8000/01-basic/
```

## Database Setup

Make sure you have the `users` table:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Next Steps

- [Foreign Keys Example](../02-relationships/foreign-keys.php) - Automatic dropdowns
- [Customization](../03-customization/metadata.php) - Control field behavior
