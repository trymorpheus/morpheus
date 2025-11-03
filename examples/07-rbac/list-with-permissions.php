<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Simulate different users - change this to test different roles
$currentUserId = isset($_GET['user']) ? (int)$_GET['user'] : 1;
$roles = [1 => 'admin', 2 => 'editor', 3 => 'author', 4 => 'user'];
$currentUserRole = $roles[$currentUserId] ?? 'guest';

$crud = new DynamicCRUD($pdo, 'blog_posts');
$crud->setCurrentUser($currentUserId, $currentUserRole);

// Handle delete
if (isset($_GET['delete'])) {
    try {
        $result = $crud->delete((int)$_GET['delete']);
        header('Location: list-with-permissions.php?user=' . $currentUserId);
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>List with Permissions - RBAC</title>
    <style>
        body { font-family: system-ui; max-width: 1400px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .user-switcher { background: #e3f2fd; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .user-switcher a { display: inline-block; padding: 8px 15px; margin: 5px; background: white; border-radius: 4px; text-decoration: none; color: #333; }
        .user-switcher a.active { background: #2196F3; color: white; font-weight: bold; }
        .role-badge { padding: 5px 15px; border-radius: 20px; font-weight: bold; font-size: 14px; }
        .role-admin { background: #f44336; color: white; }
        .role-editor { background: #2196F3; color: white; }
        .role-author { background: #4CAF50; color: white; }
        .role-user { background: #FF9800; color: white; }
        .list-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .list-header { margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #667eea; }
        .list-table { width: 100%; border-collapse: collapse; }
        .list-table th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6; }
        .list-table td { padding: 12px; border-bottom: 1px solid #dee2e6; }
        .list-table tr:hover { background: #f8f9fa; }
        .action-edit { color: #2196F3; text-decoration: none; margin-right: 10px; }
        .action-delete { color: #f44336; text-decoration: none; }
        .action-view { color: #4CAF50; text-decoration: none; margin-right: 10px; }
        .error { padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px; }
        .info-box { background: #fff3cd; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 List with Permissions</h1>
        <p>Notice how edit/delete buttons appear or disappear based on user role and row ownership.</p>
    </div>

    <div class="user-switcher">
        <strong>Switch User:</strong>
        <?php foreach ($roles as $userId => $role): ?>
            <a href="?user=<?php echo $userId; ?>" class="<?php echo $userId === $currentUserId ? 'active' : ''; ?>">
                <span class="role-badge role-<?php echo $role; ?>"><?php echo strtoupper($role); ?></span>
                User #<?php echo $userId; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (isset($error)): ?>
        <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="info-box">
        <strong>Current User:</strong> #<?php echo $currentUserId; ?> 
        <span class="role-badge role-<?php echo $currentUserRole; ?>"><?php echo strtoupper($currentUserRole); ?></span>
        <br><br>
        <strong>Permission Rules:</strong>
        <ul style="margin: 10px 0 0 20px;">
            <li><strong>Admin:</strong> Can edit and delete all posts</li>
            <li><strong>Editor:</strong> Can edit all posts, but cannot delete</li>
            <li><strong>Author:</strong> Can only edit their own posts (user_id matches)</li>
            <li><strong>User:</strong> Can only view, no edit/delete buttons</li>
        </ul>
    </div>

    <?php echo $crud->renderList(); ?>

    <div style="margin-top: 20px; padding: 15px; background: white; border-radius: 8px;">
        <h3>Try This:</h3>
        <ol>
            <li>Switch to <strong>Admin</strong> - see all edit/delete buttons</li>
            <li>Switch to <strong>Editor</strong> - edit buttons visible, delete buttons hidden</li>
            <li>Switch to <strong>Author</strong> (User #3) - only edit button on "Author Post" (their own)</li>
            <li>Switch to <strong>User</strong> - no action buttons at all</li>
        </ol>
    </div>
</body>
</html>
