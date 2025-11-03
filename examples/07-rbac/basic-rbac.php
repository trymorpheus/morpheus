<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$currentUserId = 1;
$currentUserRole = 'editor';

$crud = new DynamicCRUD($pdo, 'blog_posts');
$crud->setCurrentUser($currentUserId, $currentUserRole);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        echo "<div style='padding: 10px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; margin: 20px;'>";
        echo "✓ Post saved successfully! ID: " . $result['id'];
        echo "</div>";
    } else {
        echo "<div style='padding: 10px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px;'>";
        echo "✗ Error: " . ($result['error'] ?? 'Unknown error');
        echo "</div>";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>RBAC Example</title>
    <style>
        body { font-family: system-ui; max-width: 1200px; margin: 0 auto; padding: 20px; background: #f5f5f5; }
        .header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .user-info { display: flex; justify-content: space-between; padding: 15px; background: #e3f2fd; border-radius: 4px; margin-bottom: 20px; }
        .role-badge { padding: 5px 15px; border-radius: 20px; font-weight: bold; }
        .role-admin { background: #f44336; color: white; }
        .role-editor { background: #2196F3; color: white; }
        .permissions { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .permission-item { display: inline-block; margin: 5px; padding: 8px 15px; border-radius: 4px; }
        .permission-allowed { background: #d4edda; color: #155724; }
        .permission-denied { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 RBAC Example</h1>
    </div>

    <div class="user-info">
        <div><strong>User:</strong> #<?php echo $currentUserId; ?></div>
        <div><span class="role-badge role-<?php echo $currentUserRole; ?>"><?php echo strtoupper($currentUserRole); ?></span></div>
    </div>

    <div class="permissions">
        <h3>Permissions:</h3>
        <?php
        $pm = $crud->getPermissionManager();
        foreach (['create', 'read', 'update', 'delete'] as $perm) {
            $can = $pm->can($perm);
            $class = $can ? 'permission-allowed' : 'permission-denied';
            $icon = $can ? '✓' : '✗';
            echo "<span class='permission-item {$class}'>{$icon} " . strtoupper($perm) . "</span>";
        }
        ?>
    </div>

    <?php if ($pm->canCreate()): ?>
        <?php echo $crud->renderForm($_GET['id'] ?? null); ?>
    <?php else: ?>
        <div style="padding: 20px; background: #fff3cd; border-radius: 8px;">
            ⚠️ No permission to create posts. Role: <strong><?php echo $currentUserRole; ?></strong>
        </div>
    <?php endif; ?>
</body>
</html>
