<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cache = new FileCacheStrategy();
$crud = new DynamicCRUD($pdo, 'posts', $cache);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $action = isset($_POST['id']) ? 'actualizado' : 'creado';
        echo "<p style='color: green;'>✓ Post {$action} con ID: {$result['id']}</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . ($result['error'] ?? 'Validación fallida') . "</p>";
        if (isset($result['errors'])) {
            echo "<ul>";
            foreach ($result['errors'] as $field => $errors) {
                foreach ($errors as $error) {
                    echo "<li>{$error}</li>";
                }
            }
            echo "</ul>";
        }
    }
}

$stmt = $pdo->query('SELECT p.id, p.title, c.name as category, u.name as author 
                     FROM posts p 
                     LEFT JOIN categories c ON p.category_id = c.id
                     LEFT JOIN users u ON p.author_id = u.id
                     ORDER BY p.id DESC');
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DynamicCRUD - Posts con Claves Foráneas</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; box-sizing: border-box; }
        textarea { min-height: 100px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        h2 { margin-top: 0; }
        .badge { background: #28a745; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>
    <h1>DynamicCRUD - Posts (Fase 2)</h1>
    <p><a href="index.php">← Volver a Usuarios</a></p>
    
    <div class="container">
        <div>
            <h2><?= $id ? 'Editar Post' : 'Nuevo Post' ?></h2>
            <p class="badge">Con Claves Foráneas</p>
            <?= $crud->renderForm($id) ?>
        </div>
        <div>
            <h2>Lista de Posts</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Categoría</th>
                        <th>Autor</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['id']) ?></td>
                        <td><?= htmlspecialchars($post['title']) ?></td>
                        <td><?= htmlspecialchars($post['category']) ?></td>
                        <td><?= htmlspecialchars($post['author'] ?? 'N/A') ?></td>
                        <td><a href="?id=<?= $post['id'] ?>">Editar</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p><a href="posts.php">← Crear nuevo post</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
