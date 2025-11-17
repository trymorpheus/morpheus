<?php
/**
 * DynamicCRUD - Many-to-Many Relationships
 * 
 * Demonstrates M:N relationships with multi-select UI.
 * Example: Posts can have multiple tags, tags can belong to multiple posts.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'posts');

// Configure M:N relationship: posts ↔ tags via post_tags pivot table
$crud->addManyToMany(
    'tags',           // Field name in form
    'post_tags',      // Pivot table
    'post_id',        // Local key in pivot
    'tag_id',         // Foreign key in pivot
    'tags'            // Related table
);

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

$stmt = $pdo->query('SELECT p.id, p.title, 
                     GROUP_CONCAT(t.name SEPARATOR ", ") as tags
                     FROM posts p
                     LEFT JOIN post_tags pt ON p.id = pt.post_id
                     LEFT JOIN tags t ON pt.tag_id = t.id
                     GROUP BY p.id
                     ORDER BY p.id DESC LIMIT 10');
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Many-to-Many - DynamicCRUD</title>
    <link rel="stylesheet" href="../assets/dynamiccrud.css">
    <link rel="stylesheet" href="../assets/manytomany.css">
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
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .tag { background: #e9ecef; padding: 2px 8px; border-radius: 3px; font-size: 12px; margin-right: 4px; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <h1>🔀 Many-to-Many Relationships</h1>
    <span class="badge">Advanced M:N</span>
    <p style="color: #666; margin: 10px 0 20px 0;">
        Handle complex M:N relationships with multi-select UI and automatic pivot table management.
    </p>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <div class="info-box">
        <strong>🎯 Configuration:</strong>
        <pre style="background: white; padding: 10px; border-radius: 4px; margin-top: 10px; overflow-x: auto;"><code>$crud->addManyToMany(
    'tags',        // Field name
    'post_tags',   // Pivot table
    'post_id',     // Local key
    'tag_id',      // Foreign key
    'tags'         // Related table
);</code></pre>
        <p style="margin: 10px 0 0 0;"><strong>Result:</strong> Multi-select with checkboxes and search functionality!</p>
    </div>

    <div class="container">
        <div class="card">
            <h2><?= $id ? 'Edit Post' : 'Create Post' ?></h2>
            <p style="color: #666; font-size: 14px;">
                Select multiple tags using checkboxes. Changes are saved automatically to the pivot table.
            </p>
            <?= $crud->renderForm($id) ?>
        </div>

        <div class="card">
            <h2>Posts with Tags</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Tags</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['id']) ?></td>
                        <td><?= htmlspecialchars($post['title']) ?></td>
                        <td>
                            <?php if ($post['tags']): ?>
                                <?php foreach (explode(', ', $post['tags']) as $tag): ?>
                                    <span class="tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color: #999;">No tags</span>
                            <?php endif; ?>
                        </td>
                        <td><a href="?id=<?= $post['id'] ?>">Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p style="margin-top: 15px;"><a href="many-to-many.php">← Create new post</a></p>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>💡 How It Works</h3>
        <ol>
            <li><strong>Automatic Sync:</strong> DynamicCRUD manages the pivot table automatically</li>
            <li><strong>Transaction Safety:</strong> All changes are wrapped in a database transaction</li>
            <li><strong>UI Enhancement:</strong> Advanced checkbox UI with search functionality</li>
            <li><strong>No Manual SQL:</strong> You never write INSERT/DELETE for the pivot table</li>
        </ol>
    </div>

    <div style="margin-top: 20px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
        <h3>📚 Next Steps</h3>
        <ul>
            <li><a href="../03-customization/metadata.php">Customize Fields</a> - JSON metadata options</li>
            <li><a href="../04-advanced/hooks.php">Use Hooks</a> - Add custom logic to lifecycle events</li>
            <li><a href="../02-relationships/foreign-keys.php">Back to Foreign Keys</a></li>
        </ul>
    </div>

    <script src="../assets/dynamiccrud.js"></script>
    <script src="../assets/manytomany.js"></script>
</body>
</html>
