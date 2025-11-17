<?php
/**
 * DynamicCRUD - Hooks System
 * 
 * Demonstrates 10 lifecycle hooks for custom logic:
 * - beforeValidate, afterValidate
 * - beforeSave, afterSave
 * - beforeCreate, afterCreate
 * - beforeUpdate, afterUpdate
 * - beforeDelete, afterDelete
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'posts');

// Hook 1: Auto-generate slug from title
$crud->beforeSave(function($data) {
    if (isset($data['title']) && empty($data['slug'])) {
        $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
    }
    return $data;
});

// Hook 2: Set published_at when status changes to 'published'
$crud->afterValidate(function($data) {
    if (isset($data['status']) && $data['status'] === 'published' && empty($data['published_at'])) {
        $data['published_at'] = date('Y-m-d H:i:s');
    }
    return $data;
});

// Hook 3: Log after creating
$crud->afterCreate(function($id, $data) {
    error_log("✓ Post created: ID $id, Title: {$data['title']}");
});

// Hook 4: Log after updating
$crud->afterUpdate(function($id, $data) {
    error_log("✓ Post updated: ID $id, Title: {$data['title']}");
});

// Hook 5: Audit before deleting
$crud->beforeDelete(function($id) use ($pdo) {
    $stmt = $pdo->prepare("SELECT title FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($post) {
        error_log("⚠️ Deleting post: ID $id, Title: {$post['title']}");
    }
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $action = isset($_POST['id']) ? 'updated' : 'created';
        header("Location: ?success=Post $action with ID: {$result['id']}");
        exit;
    } else {
        $error = $result['error'] ?? 'Validation failed';
        $errors = $result['errors'] ?? [];
    }
}

if (isset($_GET['delete'])) {
    try {
        $crud->delete((int)$_GET['delete']);
        header('Location: ?success=Post deleted successfully');
        exit;
    } catch (Exception $e) {
        $error = 'Delete error: ' . $e->getMessage();
    }
}

$stmt = $pdo->query('SELECT id, title, slug, status, published_at FROM posts ORDER BY id DESC LIMIT 10');
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hooks System - DynamicCRUD</title>
    <link rel="stylesheet" href="../assets/dynamiccrud.css">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .badge { background: #6f42c1; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .info-box { background: #f3e5f5; border-left: 4px solid #9c27b0; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; margin-right: 10px; }
        a:hover { text-decoration: underline; }
        .status-published { color: #28a745; font-weight: bold; }
        .status-draft { color: #6c757d; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 13px; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <h1>🎣 Hooks System</h1>
    <span class="badge">10 Lifecycle Hooks</span>
    <p style="color: #666; margin: 10px 0 20px 0;">
        Add custom logic at any point in the CRUD lifecycle.
    </p>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <div class="info-box">
        <strong>🎯 Active Hooks in This Demo:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            <li><code>beforeSave</code> - Auto-generate slug from title</li>
            <li><code>afterValidate</code> - Set published_at when status is 'published'</li>
            <li><code>afterCreate</code> - Log to error_log when post is created</li>
            <li><code>afterUpdate</code> - Log to error_log when post is updated</li>
            <li><code>beforeDelete</code> - Audit log before deletion</li>
        </ul>
        <p style="margin: 10px 0 0 0;"><strong>💡 Tip:</strong> Check your PHP error log to see hooks in action!</p>
    </div>

    <div class="container">
        <div class="card">
            <h2><?= $id ? 'Edit Post' : 'Create Post' ?></h2>
            <p style="color: #666; font-size: 14px;">
                Leave "slug" empty - it will be generated automatically from the title!
            </p>
            <?= $crud->renderForm($id) ?>
        </div>

        <div class="card">
            <h2>Posts</h2>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['title']) ?></td>
                        <td><code><?= htmlspecialchars($post['slug'] ?? 'N/A') ?></code></td>
                        <td class="status-<?= $post['status'] ?? 'draft' ?>">
                            <?= htmlspecialchars($post['status'] ?? 'draft') ?>
                        </td>
                        <td>
                            <a href="?id=<?= $post['id'] ?>">Edit</a>
                            <a href="?delete=<?= $post['id'] ?>" onclick="return confirm('Delete this post?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p style="margin-top: 15px;"><a href="hooks.php">← Create new post</a></p>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>📋 All Available Hooks</h3>
        <table style="background: white;">
            <thead>
                <tr>
                    <th>Hook</th>
                    <th>Timing</th>
                    <th>Use Case</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><code>beforeValidate</code></td><td>Before validation</td><td>Modify data before checks</td></tr>
                <tr><td><code>afterValidate</code></td><td>After validation</td><td>Additional validation</td></tr>
                <tr><td><code>beforeSave</code></td><td>Before INSERT/UPDATE</td><td>Generate slugs, timestamps</td></tr>
                <tr><td><code>afterSave</code></td><td>After INSERT/UPDATE</td><td>Logging, notifications</td></tr>
                <tr><td><code>beforeCreate</code></td><td>Before INSERT only</td><td>Set default values</td></tr>
                <tr><td><code>afterCreate</code></td><td>After INSERT only</td><td>Send welcome email</td></tr>
                <tr><td><code>beforeUpdate</code></td><td>Before UPDATE only</td><td>Track changes</td></tr>
                <tr><td><code>afterUpdate</code></td><td>After UPDATE only</td><td>Clear cache</td></tr>
                <tr><td><code>beforeDelete</code></td><td>Before DELETE</td><td>Check dependencies</td></tr>
                <tr><td><code>afterDelete</code></td><td>After DELETE</td><td>Cleanup files</td></tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
        <h3>📚 Next Steps</h3>
        <ul>
            <li><a href="virtual-fields.php">Virtual Fields</a> - Password confirmation, terms acceptance</li>
            <li><a href="validation.php">Custom Validation</a> - Advanced validation rules</li>
            <li><a href="../05-features/i18n.php">Internationalization</a> - Multi-language support</li>
        </ul>
    </div>
    <script src="../assets/dynamiccrud.js"></script>
</body>
</html>
