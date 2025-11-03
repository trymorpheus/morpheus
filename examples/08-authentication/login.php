<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new DynamicCRUD($pdo, 'auth_users');
$crud->enableAuthentication();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleAuthentication();
    
    if ($result['success']) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = $result['error'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - DynamicCRUD Auth</title>
    <style>
        body { font-family: system-ui; max-width: 400px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .auth-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input[type="email"], input[type="password"], input[type="checkbox"] { 
            padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;
        }
        input[type="email"], input[type="password"] { width: 100%; box-sizing: border-box; }
        input[type="checkbox"] { width: auto; margin-right: 5px; }
        button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: 600; cursor: pointer; }
        button:hover { background: #5568d3; }
        .error { padding: 10px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #667eea; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
        .test-credentials { background: #e3f2fd; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; }
        .test-credentials strong { display: block; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="test-credentials">
        <strong>Test Credentials:</strong>
        Admin: admin@example.com / admin12345<br>
        User: user@example.com / user12345
    </div>
    
    <?php if ($error): ?>
        <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php echo $crud->renderLoginForm(); ?>
    
    <div class="links">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</body>
</html>
