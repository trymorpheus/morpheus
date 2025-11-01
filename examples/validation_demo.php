<?php

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cache = new FileCacheStrategy();
$crud = new DynamicCRUD($pdo, 'users', $cache);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $action = isset($_POST['id']) ? 'actualizado' : 'creado';
        echo "<p style='color: green;'>✓ Usuario {$action} con ID: {$result['id']}</p>";
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

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DynamicCRUD - Validación Cliente</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 0 20px; }
        h1 { color: #333; }
        .badge { background: #28a745; color: white; padding: 4px 12px; border-radius: 4px; font-size: 14px; }
        .info { background: #e7f3ff; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0; }
        .info h3 { margin-top: 0; color: #007bff; }
        .info ul { margin: 10px 0; padding-left: 20px; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; }
        textarea { min-height: 80px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>DynamicCRUD - Fase 3</h1>
    <p class="badge">Validación Cliente en Tiempo Real</p>
    
    <div class="info">
        <h3>🎯 Prueba la validación en tiempo real:</h3>
        <ul>
            <li>Deja campos requeridos en blanco y haz clic fuera</li>
            <li>Escribe un email inválido (sin @)</li>
            <li>Escribe una URL inválida (sin http://)</li>
            <li>Excede el límite de caracteres</li>
            <li>Los errores aparecen inmediatamente</li>
        </ul>
    </div>
    
    <?= $crud->renderForm($id) ?>
    
    <p style="margin-top: 30px;">
        <a href="index.php">← Volver a usuarios</a> | 
        <a href="posts.php">Posts</a> | 
        <a href="categories.php">Categorías</a>
    </p>
</body>
</html>
