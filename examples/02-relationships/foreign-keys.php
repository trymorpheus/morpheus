<?php
/**
 * DynamicCRUD - Foreign Keys Example
 * 
 * Demonstrates automatic foreign key detection and dropdown generation.
 * No configuration needed - DynamicCRUD reads your database relationships!
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Posts table has a foreign key to categories
// DynamicCRUD automatically creates a dropdown!
$crud = new Morpheus($pdo, 'posts');

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

$stmt = $pdo->query('SELECT p.id, p.title, p.slug, c.name as category 
                     FROM posts p 
                     LEFT JOIN categories c ON p.category_id = c.id
                     ORDER BY p.id DESC LIMIT 10');
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foreign Keys - DynamicCRUD</title>
    <link rel="stylesheet" href="../assets/dynamiccrud.css">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .badge { background: #17a2b8; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .info-box { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <h1>🔗 Foreign Keys - Automatic Dropdowns</h1>
    <span class="badge">Zero Configuration</span>
    <p style="color: #666; margin: 10px 0 20px 0;">
        DynamicCRUD automatically detects foreign keys and creates dropdown selects.
    </p>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <div class="info-box">
        <strong>🎯 How it works:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            <li>DynamicCRUD reads foreign key constraints from your database</li>
            <li>Automatically creates <code>&lt;select&gt;</code> dropdowns for FK fields</li>
            <li>Shows the <code>name</code> column by default (customizable via metadata)</li>
            <li>Handles nullable FKs with an empty option</li>
        </ul>
    </div>

    <div class="container">
        <div class="card">
            <h2><?= $id ? 'Edit Post' : 'Create Post' ?></h2>
            <p style="color: #666; font-size: 14px;">
                Notice the "Category" field is automatically a dropdown! 🎉
            </p>
            <?= $crud->renderForm($id) ?>
        </div>

        <div class="card">
            <h2>Posts List</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['id']) ?></td>
                        <td><?= htmlspecialchars($post['title']) ?></td>
                        <td><?= htmlspecialchars($post['category'] ?? 'N/A') ?></td>
                        <td><a href="?id=<?= $post['id'] ?>">Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p style="margin-top: 15px;"><a href="foreign-keys.php">← Create new post</a></p>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>💡 Customize Display Column</h3>
        <p>By default, DynamicCRUD shows the <code>name</code> column. Change it with metadata:</p>
        <pre style="background: white; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>ALTER TABLE posts 
MODIFY COLUMN category_id INT 
COMMENT '{"display_column": "title"}';</code></pre>
    </div>

    <div style="margin-top: 20px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
        <h3>📚 Next Steps</h3>
        <ul>
            <li><a href="many-to-many.php">Many-to-Many Relationships</a> - Multi-select for M:N relations</li>
            <li><a href="../03-customization/metadata.php">Metadata Customization</a> - Control field behavior</li>
            <li><a href="../01-basic/">Back to Basic Example</a></li>
        </ul>
    </div>
    <script src="../assets/dynamiccrud.js"></script>
</body>
</html>
