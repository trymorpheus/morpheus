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

if (isset($_GET['logout'])) {
    $crud->handleAuthentication();
    header('Location: login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - DynamicCRUD Auth</title>
    <style>
        body { font-family: system-ui; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .dashboard { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #333; }
        .user-info { background: #e3f2fd; padding: 20px; border-radius: 4px; margin-bottom: 20px; }
        .user-info strong { display: block; margin-bottom: 10px; font-size: 18px; }
        .user-info p { margin: 5px 0; }
        .role-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 14px; }
        .role-admin { background: #f44336; color: white; }
        .role-user { background: #4CAF50; color: white; }
        .actions { margin-top: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        .btn:hover { background: #5568d3; }
        .btn-danger { background: #f44336; }
        .btn-danger:hover { background: #d32f2f; }
    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Welcome to Dashboard</h1>
        
        <div class="user-info">
            <strong>User Information</strong>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Role:</strong> <span class="role-badge role-<?php echo $user['role']; ?>"><?php echo strtoupper($user['role']); ?></span></p>
            <p><strong>Member since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
        </div>
        
        <div class="actions">
            <a href="profile.php" class="btn">Edit Profile</a>
            <form method="POST" action="?logout=1" style="display: inline;">
                <input type="hidden" name="action" value="logout">
                <button type="submit" class="btn btn-danger">Logout</button>
            </form>
        </div>
    </div>
</body>
</html>
