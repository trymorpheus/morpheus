<?php
session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cache = new FileCacheStrategy();
$crud = new DynamicCRUD($pdo, 'posts', $cache);

// Definir relación Muchos a Muchos con tags
$crud->addManyToMany(
    'tags',              // Nombre del campo en el formulario
    'posts_tags',        // Tabla pivote
    'post_id',           // Clave local (posts.id)
    'tag_id',            // Clave foránea (tags.id)
    'tags'               // Tabla relacionada
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $action = isset($_POST['id']) ? 'actualizado' : 'creado';
        header('Location: many_to_many_demo.php?success=' . urlencode("Post {$action} con ID: {$result['id']}"));
        exit;
    } else {
        $error = $result['error'] ?? 'Validación fallida';
        $errors = $result['errors'] ?? [];
    }
}

// Obtener posts con sus tags
$stmt = $pdo->query('
    SELECT p.id, p.title, p.status, c.name as category,
           GROUP_CONCAT(t.name SEPARATOR ", ") as tags
    FROM posts p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN posts_tags pt ON p.id = pt.post_id
    LEFT JOIN tags t ON pt.tag_id = t.id
    GROUP BY p.id
    ORDER BY p.id DESC
');
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DynamicCRUD - Relaciones M:N (Fase 4)</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 50px auto; padding: 0 20px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        h2 { margin-top: 0; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; margin-right: 10px; }
        a:hover { text-decoration: underline; }
        .badge { background: #17a2b8; color: white; padding: 4px 12px; border-radius: 3px; font-size: 12px; display: inline-block; margin-bottom: 10px; }
        .nav { margin-bottom: 20px; padding: 10px 0; border-bottom: 2px solid #eee; }
        .nav a { margin-right: 15px; }
        .info-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; }
        .info-box h3 { margin-top: 0; color: #856404; }
        .tags { display: inline-flex; gap: 5px; flex-wrap: wrap; }
        .tag { background: #e7f3ff; color: #0066cc; padding: 2px 8px; border-radius: 3px; font-size: 12px; }
        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <h1>🔗 DynamicCRUD - Relaciones Muchos a Muchos (Fase 4)</h1>
    <p class="badge">Posts con Tags (M:N)</p>
    
    <div class="nav">
        <a href="index.php">Usuarios</a> |
        <a href="posts.php">Posts</a> |
        <a href="hooks_demo.php">Hooks Demo</a> |
        <strong>M:N Demo</strong>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success" role="alert">
            <span class="alert-icon" aria-hidden="true">✓</span>
            <span><?= htmlspecialchars($_GET['success']) ?></span>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error" role="alert">
            <span class="alert-icon" aria-hidden="true">✗</span>
            <div>
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="info-box">
        <h3>📌 Relación Muchos a Muchos</h3>
        <p>Un <strong>Post</strong> puede tener múltiples <strong>Tags</strong>, y un <strong>Tag</strong> puede estar en múltiples <strong>Posts</strong>.</p>
        <p>La relación se almacena en la tabla pivote <code>posts_tags</code>.</p>
        <p><strong>💡 Tip:</strong> Mantén presionado Ctrl (Cmd en Mac) para seleccionar múltiples tags.</p>
    </div>
    
    <div class="container">
        <div>
            <h2><?= $id ? 'Editar Post' : 'Nuevo Post' ?></h2>
            <?= $crud->renderForm($id) ?>
        </div>
        <div>
            <h2>Posts con Tags</h2>
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Tags</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['title']) ?></td>
                        <td>
                            <?php if ($post['tags']): ?>
                                <div class="tags">
                                    <?php foreach (explode(', ', $post['tags']) as $tag): ?>
                                        <span class="tag"><?= htmlspecialchars($tag) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span style="color: #999;">Sin tags</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?id=<?= $post['id'] ?>">Editar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p style="margin-top: 15px;"><a href="many_to_many_demo.php">← Crear nuevo post</a></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 4px;">
        <h3>📚 Código de Ejemplo:</h3>
        <pre style="background: #fff; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>$crud = new DynamicCRUD($pdo, 'posts');

// Definir relación M:N
$crud->addManyToMany(
    'tags',           // Nombre del campo
    'posts_tags',     // Tabla pivote
    'post_id',        // Clave local
    'tag_id',         // Clave foránea
    'tags'            // Tabla relacionada
);

$crud->handleSubmission();</code></pre>
    </div>
</body>
</html>
