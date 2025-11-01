<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;
use DynamicCRUD\ListGenerator;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cache = new FileCacheStrategy();
$crud = new DynamicCRUD($pdo, 'categories', $cache);

// Manejar eliminación
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($crud->delete($id)) {
        header('Location: categories.php?msg=deleted');
        exit;
    }
}

// Manejar envío de formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        header('Location: categories.php?msg=saved');
        exit;
    } else {
        $error = $result['error'] ?? 'Validación fallida';
        $errors = $result['errors'] ?? [];
    }
}

// Obtener listado con paginación
$page = $_GET['page'] ?? 1;
$result = $crud->list([
    'page' => $page,
    'perPage' => 10,
    'sort' => ['id' => 'DESC']
]);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DynamicCRUD - Categorías</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; }
        .container { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; box-sizing: border-box; }
        textarea { min-height: 80px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        .crud-table { width: 100%; border-collapse: collapse; }
        .crud-table th, .crud-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .crud-table th { background: #f8f9fa; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        h2 { margin-top: 0; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .pagination { margin-top: 15px; text-align: center; }
        .pagination a { margin: 0 5px; }
        .badge { background: #17a2b8; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; }
    </style>
</head>
<body>
    <h1>DynamicCRUD - Categorías</h1>
    <p><a href="index.php">Usuarios</a> | <a href="posts.php">Posts</a> | <strong>Categorías</strong></p>
    
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">
            <?= $_GET['msg'] === 'saved' ? '✓ Categoría guardada exitosamente' : '✓ Categoría eliminada' ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            ✗ <?= htmlspecialchars($error) ?>
            <?php if (!empty($errors)): ?>
                <ul>
                    <?php foreach ($errors as $field => $fieldErrors): ?>
                        <?php foreach ($fieldErrors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="container">
        <div>
            <h2><?= $id ? 'Editar Categoría' : 'Nueva Categoría' ?></h2>
            <p class="badge">Con Paginación y DELETE</p>
            <?= $crud->renderForm($id) ?>
            <?php if ($id): ?>
                <p><a href="categories.php">← Crear nueva categoría</a></p>
            <?php endif; ?>
        </div>
        <div>
            <h2>Lista de Categorías</h2>
            <?php
            $listGen = new ListGenerator($pdo, $crud->list()['data'] ? ['table' => 'categories', 'columns' => [], 'primary_key' => 'id', 'foreign_keys' => []] : []);
            
            // Renderizar tabla manualmente para este ejemplo
            if (!empty($result['data'])):
            ?>
                <table class="crud-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['data'] as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars(substr($row['description'] ?? '', 0, 50)) ?></td>
                            <td>
                                <a href="?id=<?= $row['id'] ?>">Editar</a> | 
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('¿Eliminar esta categoría?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <?php if ($result['pagination']['page'] > 1): ?>
                        <a href="?page=<?= $result['pagination']['page'] - 1 ?>">« Anterior</a>
                    <?php endif; ?>
                    
                    <span>Página <?= $result['pagination']['page'] ?> de <?= $result['pagination']['totalPages'] ?></span>
                    
                    <?php if ($result['pagination']['page'] < $result['pagination']['totalPages']): ?>
                        <a href="?page=<?= $result['pagination']['page'] + 1 ?>">Siguiente »</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No hay categorías para mostrar.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
