<?php
/**
 * DynamicCRUD - Basic Example
 * 
 * This is the simplest possible CRUD implementation.
 * Just 3 lines of code for a complete Create/Read/Update/Delete system!
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

// 1. Connect to database
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 2. Create CRUD instance
$crud = new Morpheus($pdo, 'users');

// 3. Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $action = isset($_POST['id']) ? 'updated' : 'created';
        header("Location: ?success=User $action with ID: {$result['id']}");
        exit;
    } else {
        $error = $result['error'] ?? 'Validation failed';
        $errors = $result['errors'] ?? [];
    }
}

// Get users list
$stmt = $pdo->query('SELECT id, name, email FROM users ORDER BY id DESC LIMIT 10');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DynamicCRUD - Basic Example</title>
    <link rel="stylesheet" href="../assets/dynamiccrud.css">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .header { margin-bottom: 30px; }
        .badge { background: #28a745; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .code-box { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-top: 20px; }
        .code-box code { font-family: 'Courier New', monospace; font-size: 13px; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚀 DynamicCRUD - Basic Example</h1>
        <span class="badge">3 Lines of Code</span>
        <p style="color: #666; margin-top: 10px;">
            Complete CRUD system with zero configuration. Forms are generated automatically from your database schema.
        </p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            ✗ Error: <?= htmlspecialchars($error) ?>
            <?php if (!empty($errors)): ?>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <?php foreach ($errors as $field => $fieldErrors): ?>
                        <?php foreach ($fieldErrors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <div class="card">
            <h2><?= $id ? 'Edit User' : 'Create User' ?></h2>
            <?= $crud->renderForm($id) ?>
        </div>

        <div class="card">
            <h2>Users List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><a href="?id=<?= $user['id'] ?>">Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p style="margin-top: 15px;"><a href="index.php">← Create new user</a></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="code-box">
        <strong>💡 That's all the code you need:</strong>
        <pre><code>$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'pass');
$crud = new Morpheus($pdo, 'users');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
}

echo $crud->renderForm($_GET['id'] ?? null);</code></pre>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
        <h3>📚 Next Steps</h3>
        <ul>
            <li><a href="../02-relationships/foreign-keys.php">Learn about Foreign Keys</a> - Automatic dropdowns for related data</li>
            <li><a href="../02-relationships/many-to-many.php">Many-to-Many Relationships</a> - Multi-select for M:N relations</li>
            <li><a href="../03-customization/metadata.php">Customize with Metadata</a> - Control field types, validation, and more</li>
        </ul>
    </div>
    
    <script src="../assets/dynamiccrud.js"></script>
</body>
</html>
