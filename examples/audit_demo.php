<?php
session_start();

// Simular usuario logueado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Usuario de prueba
    $_SESSION['username'] = 'admin';
}

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cache = new FileCacheStrategy();
$crud = new DynamicCRUD($pdo, 'posts', $cache);

// Habilitar auditoría
$crud->enableAudit($_SESSION['user_id']);

// Hook: Generar slug automáticamente
$crud->beforeSave(function($data) {
    if (isset($data['title']) && empty($data['slug'])) {
        $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
    }
    return $data;
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $action = isset($_POST['id']) ? 'actualizado' : 'creado';
        header('Location: audit_demo.php?success=' . urlencode("Post {$action} con ID: {$result['id']}"));
        exit;
    } else {
        $error = $result['error'] ?? 'Validación fallida';
        $errors = $result['errors'] ?? [];
    }
}

// Manejar eliminación
if (isset($_GET['delete'])) {
    try {
        $crud->delete((int)$_GET['delete']);
        header('Location: audit_demo.php?success=' . urlencode('Post eliminado correctamente'));
        exit;
    } catch (Exception $e) {
        $error = 'Error al eliminar: ' . $e->getMessage();
    }
}

$stmt = $pdo->query('SELECT p.id, p.title, p.status, c.name as category 
                     FROM posts p 
                     LEFT JOIN categories c ON p.category_id = c.id
                     ORDER BY p.id DESC LIMIT 10');
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener historial de auditoría si se solicita
$auditHistory = [];
if (isset($_GET['history'])) {
    $auditHistory = $crud->getAuditHistory((int)$_GET['history']);
}

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DynamicCRUD - Sistema de Auditoría (Fase 4)</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1200px; margin: 50px auto; padding: 0 20px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        h2 { margin-top: 0; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; margin-right: 10px; }
        a:hover { text-decoration: underline; }
        .badge { background: #28a745; color: white; padding: 4px 12px; border-radius: 3px; font-size: 12px; display: inline-block; margin-bottom: 10px; }
        .nav { margin-bottom: 20px; padding: 10px 0; border-bottom: 2px solid #eee; }
        .nav a { margin-right: 15px; }
        .info-box { background: #d1ecf1; border-left: 4px solid #0c5460; padding: 15px; margin-bottom: 20px; }
        .info-box h3 { margin-top: 0; color: #0c5460; }
        .audit-entry { background: #f8f9fa; padding: 10px; margin: 10px 0; border-left: 3px solid #6c757d; }
        .audit-create { border-left-color: #28a745; }
        .audit-update { border-left-color: #ffc107; }
        .audit-delete { border-left-color: #dc3545; }
        .json-data { background: #fff; padding: 8px; border-radius: 3px; font-family: monospace; font-size: 12px; overflow-x: auto; }
        @media (max-width: 768px) {
            .container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <h1>📋 DynamicCRUD - Sistema de Auditoría (Fase 4)</h1>
    <p class="badge">Registro Automático de Cambios</p>
    
    <div class="nav">
        <a href="index.php">Usuarios</a> |
        <a href="hooks_demo.php">Hooks Demo</a> |
        <a href="many_to_many_demo.php">M:N Demo</a> |
        <strong>Auditoría Demo</strong>
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
            <div><strong>Error:</strong> <?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>
    
    <div class="info-box">
        <h3>🔍 Sistema de Auditoría Activo</h3>
        <p>Todos los cambios (CREATE, UPDATE, DELETE) se registran automáticamente en la tabla <code>audit_log</code>.</p>
        <p><strong>Usuario actual:</strong> <?= htmlspecialchars($_SESSION['username']) ?> (ID: <?= $_SESSION['user_id'] ?>)</p>
        <p><strong>💡 Tip:</strong> Haz clic en "Ver historial" para ver todos los cambios de un post.</p>
    </div>
    
    <?php if (!empty($auditHistory)): ?>
        <div style="background: #fff; padding: 20px; border: 1px solid #ddd; margin-bottom: 20px;">
            <h3>📜 Historial de Auditoría - Post ID: <?= $_GET['history'] ?></h3>
            <a href="audit_demo.php">← Volver</a>
            
            <?php foreach ($auditHistory as $entry): ?>
                <div class="audit-entry audit-<?= strtolower($entry['action']) ?>">
                    <strong><?= $entry['action'] ?></strong> - 
                    <?= date('d/m/Y H:i:s', strtotime($entry['created_at'])) ?> - 
                    Usuario ID: <?= $entry['user_id'] ?? 'N/A' ?> - 
                    IP: <?= $entry['user_ip'] ?? 'N/A' ?>
                    
                    <?php if ($entry['old_values']): ?>
                        <details>
                            <summary>Valores anteriores</summary>
                            <div class="json-data"><?= htmlspecialchars(json_encode(json_decode($entry['old_values']), JSON_PRETTY_PRINT)) ?></div>
                        </details>
                    <?php endif; ?>
                    
                    <?php if ($entry['new_values']): ?>
                        <details>
                            <summary>Valores nuevos</summary>
                            <div class="json-data"><?= htmlspecialchars(json_encode(json_decode($entry['new_values']), JSON_PRETTY_PRINT)) ?></div>
                        </details>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="container">
        <div>
            <h2><?= $id ? 'Editar Post' : 'Nuevo Post' ?></h2>
            <?= $crud->renderForm($id) ?>
        </div>
        <div>
            <h2>Posts Recientes</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= $post['id'] ?></td>
                        <td><?= htmlspecialchars($post['title']) ?></td>
                        <td>
                            <a href="?id=<?= $post['id'] ?>">Editar</a>
                            <a href="?history=<?= $post['id'] ?>">Ver historial</a>
                            <a href="?delete=<?= $post['id'] ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 4px;">
        <h3>📚 Código de Ejemplo:</h3>
        <pre style="background: #fff; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>$crud = new DynamicCRUD($pdo, 'posts');

// Habilitar auditoría
$crud->enableAudit($_SESSION['user_id']);

// Todos los cambios se registran automáticamente
$crud->handleSubmission();

// Ver historial de un registro
$history = $crud->getAuditHistory($postId);</code></pre>
    </div>
</body>
</html>
