<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'auth_users');
$crud->enableAuthentication();

$message = null;
$resetLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->requestPasswordReset($_POST['email'] ?? '');
    
    if ($result['success']) {
        $resetLink = 'http://localhost:8000/examples/08-authentication/reset-password.php?token=' . $result['token'];
        $message = ['type' => 'success', 'text' => 'Password reset link generated!'];
    } else {
        $message = ['type' => 'error', 'text' => $result['error']];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - DynamicCRUD</title>
    <style>
        body { font-family: system-ui; max-width: 400px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .auth-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #555; }
        input[type="email"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 4px; font-size: 16px; font-weight: 600; cursor: pointer; }
        button:hover { background: #5568d3; }
        .message { padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .message.success { background: #d4edda; color: #155724; }
        .message.error { background: #f8d7da; color: #721c24; }
        .reset-link { background: #e3f2fd; padding: 15px; border-radius: 4px; margin-top: 20px; word-break: break-all; }
        .reset-link strong { display: block; margin-bottom: 10px; }
        .reset-link a { color: #667eea; text-decoration: none; }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #667eea; text-decoration: none; }
        .links a:hover { text-decoration: underline; }
        .info { background: #fff3cd; padding: 15px; border-radius: 4px; margin-bottom: 20px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2>🔑 Forgot Password</h2>
        
        <div class="info">
            <strong>ℹ️ Demo Mode:</strong> In production, this would send an email. For now, we'll show the reset link on screen.
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo $message['type']; ?>">
                <?php echo $message['type'] === 'success' ? '✅' : '❌'; ?> <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($resetLink): ?>
            <div class="reset-link">
                <strong>🔗 Password Reset Link:</strong>
                <a href="<?php echo htmlspecialchars($resetLink); ?>"><?php echo htmlspecialchars($resetLink); ?></a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" id="email" required placeholder="Enter your email">
                </div>
                
                <button type="submit">Send Reset Link</button>
            </form>
        <?php endif; ?>
        
        <div class="links">
            <a href="login.php">← Back to Login</a>
        </div>
    </div>
</body>
</html>
