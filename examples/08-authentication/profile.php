<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new DynamicCRUD($pdo, 'auth_users');
$crud->enableAuthentication();

if (!$crud->isAuthenticated()) {
    header('Location: login.php');
    exit;
}

$user = $crud->getCurrentUser();
$crud->setCurrentUser($user['id'], $user['role']);

$success = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $success = 'Profile updated successfully!';
        $user = $crud->getCurrentUser();
    } else {
        $error = $result['error'] ?? 'Failed to update profile';
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - DynamicCRUD Auth</title>
    <style>
        body { font-family: system-ui; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .profile { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #333; }
        .success { padding: 10px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px; }
        .error { padding: 10px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px; }
        .back-link { display: inline-block; margin-bottom: 20px; color: #667eea; text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="profile">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        
        <h1>Edit Profile</h1>
        
        <?php if ($success): ?>
            <div class="success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php echo $crud->renderForm($user['id']); ?>
    </div>
</body>
</html>
