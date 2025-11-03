<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new DynamicCRUD($pdo, 'soft_posts');

// Handle restore
if (isset($_GET['restore'])) {
    $crud->restore((int)$_GET['restore']);
    header('Location: index.php');
    exit;
}

// Handle force delete
if (isset($_GET['force_delete'])) {
    if (confirm('¿Eliminar permanentemente?')) {
        $crud->forceDelete((int)$_GET['force_delete']);
    }
    header('Location: index.php');
    exit;
}

// Handle delete (soft delete)
if (isset($_GET['delete'])) {
    $crud->delete((int)$_GET['delete']);
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    if ($result['success']) {
        header('Location: index.php');
        exit;
    }
    $error = $result['error'] ?? 'Error al guardar';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Soft Deletes - DynamicCRUD</title>
    <style>
        body { font-family: system-ui; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h1 { color: #333; margin-top: 0; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 4px; margin-bottom: 20px; border-left: 4px solid #2196f3; }
        .actions { margin: 20px 0; }
        .actions a { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        .actions a:hover { background: #5568d3; }
        .actions a.secondary { background: #6c757d; }
        .actions a.secondary:hover { background: #5a6268; }
        .actions a.danger { background: #dc3545; }
        .actions a.danger:hover { background: #c82333; }
        .deleted { background: #fff3cd; border-left: 4px solid #ffc107; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .action-edit, .action-delete, .action-restore, .action-force-delete { padding: 5px 10px; margin-right: 5px; text-decoration: none; border-radius: 3px; font-size: 14px; }
        .action-edit { background: #28a745; color: white; }
        .action-delete { background: #ffc107; color: #000; }
        .action-restore { background: #17a2b8; color: white; }
        .action-force-delete { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📝 Soft Deletes Example</h1>
        
        <div class="info">
            <strong>ℹ️ Soft Deletes:</strong> Los registros eliminados no se borran de la base de datos, solo se marcan con <code>deleted_at</code>.
            Puedes restaurarlos o eliminarlos permanentemente.
        </div>
        
        <div class="actions">
            <a href="?create=1">➕ Crear Post</a>
            <a href="?withTrashed=1" class="secondary">🗑️ Ver Eliminados</a>
            <a href="index.php" class="secondary">👁️ Ver Activos</a>
        </div>
    </div>
    
    <?php if (isset($_GET['create']) || isset($_GET['id'])): ?>
        <div class="container">
            <h2><?php echo isset($_GET['id']) ? 'Editar Post' : 'Crear Post'; ?></h2>
            <?php if (isset($error)): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                    ❌ <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php echo $crud->renderForm($_GET['id'] ?? null); ?>
        </div>
    <?php else: ?>
        <div class="container">
            <h2>Lista de Posts</h2>
            <?php
            // Custom list with restore/force delete actions
            $withTrashed = isset($_GET['withTrashed']);
            $page = $_GET['page'] ?? 1;
            $perPage = 10;
            $offset = ($page - 1) * $perPage;
            
            $sql = "SELECT * FROM soft_posts";
            if (!$withTrashed) {
                $sql .= " WHERE deleted_at IS NULL";
            }
            $sql .= " ORDER BY id DESC LIMIT $perPage OFFSET $offset";
            
            $stmt = $pdo->query($sql);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($posts)) {
                echo '<p>No hay posts.</p>';
            } else {
                echo '<table>';
                echo '<thead><tr><th>ID</th><th>Título</th><th>Autor</th><th>Creado</th><th>Eliminado</th><th>Acciones</th></tr></thead>';
                echo '<tbody>';
                foreach ($posts as $post) {
                    $isDeleted = !empty($post['deleted_at']);
                    $rowClass = $isDeleted ? ' class="deleted"' : '';
                    echo "<tr$rowClass>";
                    echo '<td>' . $post['id'] . '</td>';
                    echo '<td>' . htmlspecialchars($post['title']) . '</td>';
                    echo '<td>' . htmlspecialchars($post['author']) . '</td>';
                    echo '<td>' . $post['created_at'] . '</td>';
                    echo '<td>' . ($post['deleted_at'] ?? '-') . '</td>';
                    echo '<td>';
                    
                    if ($isDeleted) {
                        echo '<a href="?restore=' . $post['id'] . '" class="action-restore">♻️ Restaurar</a>';
                        echo '<a href="?force_delete=' . $post['id'] . '" class="action-force-delete" onclick="return confirm(\'¿Eliminar PERMANENTEMENTE?\')">🗑️ Eliminar Permanente</a>';
                    } else {
                        echo '<a href="?id=' . $post['id'] . '" class="action-edit">✏️ Editar</a>';
                        echo '<a href="?delete=' . $post['id'] . '" class="action-delete" onclick="return confirm(\'¿Eliminar?\')">🗑️ Eliminar</a>';
                    }
                    
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
            }
            ?>
        </div>
    <?php endif; ?>
</body>
</html>
