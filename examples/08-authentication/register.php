<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'auth_users');
$crud->enableAuthentication();

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleAuthentication();
    
    if ($result['success']) {
        $success = 'Registration successful! Redirecting...';
        header('refresh:2;url=dashboard.php');
    } else {
        $error = $result['error'];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - DynamicCRUD Auth</title>
    <style>
        body { font-family: system-ui; max-width: 400px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .auth-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input[type="text"], input[type="email"], input[type="password"] { 
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;
        }
        button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: 600; cursor: pointer; }
        button:hover { background: #5568d3; }
        .error { padding: 10px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px; }
        .success { padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #667eea; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <?php if ($error): ?>
        <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success">✅ <?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php echo $crud->renderRegistrationForm(); ?>
    
    <div class="links">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</body>
</html>
