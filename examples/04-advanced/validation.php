<?php
/**
 * DynamicCRUD - Custom Validation
 * 
 * Demonstrates custom validation rules using hooks.
 * Combine with built-in validation for powerful data integrity.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'users');

// Custom validation: Email must be from allowed domains
$crud->afterValidate(function($data) {
    $allowedDomains = ['example.com', 'test.com', 'demo.com'];
    
    if (isset($data['email'])) {
        $domain = substr(strrchr($data['email'], "@"), 1);
        if (!in_array($domain, $allowedDomains)) {
            throw new \Exception("Email must be from: " . implode(', ', $allowedDomains));
        }
    }
    
    return $data;
});

// Custom validation: Name must not contain numbers
$crud->afterValidate(function($data) {
    if (isset($data['name']) && preg_match('/\d/', $data['name'])) {
        throw new \Exception("Name cannot contain numbers");
    }
    return $data;
});

// Custom validation: Password strength check
$crud->afterValidate(function($data) {
    if (isset($data['password']) && !empty($data['password'])) {
        $password = $data['password'];
        
        if (strlen($password) < 8) {
            throw new \Exception("Password must be at least 8 characters");
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            throw new \Exception("Password must contain at least one uppercase letter");
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            throw new \Exception("Password must contain at least one lowercase letter");
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            throw new \Exception("Password must contain at least one number");
        }
    }
    
    return $data;
});

// Hash password before saving
$crud->beforeSave(function($data) {
    if (isset($data['password']) && !empty($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    return $data;
});

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

$stmt = $pdo->query('SELECT id, name, email FROM users ORDER BY id DESC LIMIT 10');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Validation - DynamicCRUD</title>
    <link rel="stylesheet" href="../assets/dynamiccrud.css">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .badge { background: #ffc107; color: #000; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 13px; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <h1>✅ Custom Validation</h1>
    <span class="badge">Advanced Rules</span>
    <p style="color: #666; margin: 10px 0 20px 0;">
        Add custom validation logic using the afterValidate hook.
    </p>

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

    <div class="info-box">
        <strong>🎯 Custom Validation Rules:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            <li><strong>Email domain:</strong> Must be from example.com, test.com, or demo.com</li>
            <li><strong>Name:</strong> Cannot contain numbers</li>
            <li><strong>Password:</strong> Min 8 chars, 1 uppercase, 1 lowercase, 1 number</li>
        </ul>
    </div>

    <div class="container">
        <div class="card">
            <h2><?= $id ? 'Edit User' : 'Create User' ?></h2>
            <p style="color: #666; font-size: 14px;">
                Try submitting with invalid data to see custom validation in action!
            </p>
            <?= $crud->renderForm($id) ?>
        </div>

        <div class="card">
            <h2>Users</h2>
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
                <p style="margin-top: 15px;"><a href="validation.php">← Create new user</a></p>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>💡 Code Example</h3>
        <pre style="background: white; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>// Email domain validation
$crud->afterValidate(function($data) {
    $allowedDomains = ['example.com', 'test.com'];
    $domain = substr(strrchr($data['email'], "@"), 1);
    
    if (!in_array($domain, $allowedDomains)) {
        throw new \Exception("Email must be from allowed domains");
    }
    
    return $data;
});

// Password strength validation
$crud->afterValidate(function($data) {
    if (isset($data['password'])) {
        if (!preg_match('/[A-Z]/', $data['password'])) {
            throw new \Exception("Password must contain uppercase");
        }
    }
    return $data;
});</code></pre>
    </div>

    <div style="margin-top: 20px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
        <h3>📚 Next Steps</h3>
        <ul>
            <li><a href="../05-features/i18n.php">Internationalization</a> - Multi-language support</li>
            <li><a href="../05-features/templates.php">Template System</a> - Custom layouts</li>
            <li><a href="virtual-fields.php">Back to Virtual Fields</a></li>
        </ul>
    </div>
    <script src="../assets/dynamiccrud.js"></script>
</body>
</html>
