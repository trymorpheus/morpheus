<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'auth_users');
$crud->enableAuthentication();

$token = $_GET['token'] ?? '';
$message = null;

if (empty($token)) {
    header('Location: forgot-password.php');
    exit;
}

$email = $crud->validateResetToken($token);

if (!$email) {
    $message = ['type' => 'error', 'text' => 'Invalid or expired reset token'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $email) {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || strlen($password) < 8) {
        $message = ['type' => 'error', 'text' => 'Password must be at least 8 characters'];
    } elseif ($password !== $confirmPassword) {
        $message = ['type' => 'error', 'text' => 'Passwords do not match'];
    } else {
        $result = $crud->resetPassword($token, $password);
        
        if ($result['success']) {
            $message = ['type' => 'success', 'text' => 'Password reset successfully! You can now login.'];
            $email = null; // Hide form
        } else {
            $message = ['type' => 'error', 'text' => $result['error']];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - DynamicCRUD</title>
    <style>
        body { font-family: system-ui; max-width: 400px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .auth-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: 600; cursor: pointer; }
        button:hover { background: #5568d3; }
        .message { padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .message.success { background: #d4edda; color: #155724; }
        .message.error { background: #f8d7da; color: #721c24; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #667eea; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2>🔐 Reset Password</h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message['type']; ?>">
                <?php echo $message['type'] === 'success' ? '✅' : '❌'; ?> <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($email): ?>
            <div class="info">
                <strong>📧 Resetting password for:</strong> <?php echo htmlspecialchars($email); ?>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" name="password" id="password" required minlength="8" placeholder="At least 8 characters">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required minlength="8" placeholder="Repeat password">
                </div>
                
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
        
        <div class="links">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>
