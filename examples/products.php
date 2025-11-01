<?php
session_start();
unset($_SESSION['_csrf_token']); // Limpiar token antiguo

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Cache\FileCacheStrategy;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$cache = new FileCacheStrategy();
$crud = new DynamicCRUD($pdo, 'products', $cache);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $action = isset($_POST['id']) ? 'actualizado' : 'creado';
        header('Location: products.php?success=' . urlencode("Producto {$action} con ID: {$result['id']}"));
        exit;
    } else {
        $error = $result['error'] ?? 'Validación fallida';
        $errors = $result['errors'] ?? [];
    }
}

$stmt = $pdo->query('SELECT p.id, p.name, p.price, p.image, c.name as category 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id
                     ORDER BY p.id DESC');
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DynamicCRUD - Productos con Imágenes</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea, select { width: 100%; padding: 8px; box-sizing: border-box; }
        textarea { min-height: 80px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        h2 { margin-top: 0; }
        .badge { background: #17a2b8; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px; }
        .product-img { max-width: 50px; height: auto; }
    </style>
</head>
<body>
    <h1>DynamicCRUD - Productos (Fase 3)</h1>
    <p class="badge">Con Subida de Archivos</p>
    <p><a href="index.php">Usuarios</a> | <a href="posts.php">Posts</a> | <a href="categories.php">Categorías</a> | <strong>Productos</strong></p>
    
    <?php if (isset($_GET['success'])): ?>
        <p style='color: green;'>✓ <?= htmlspecialchars($_GET['success']) ?></p>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <p style='color: red;'>✗ Error: <?= htmlspecialchars($error) ?></p>
        <?php if (!empty($errors)): ?>
            <ul>
                <?php foreach ($errors as $field => $fieldErrors): ?>
                    <?php foreach ($fieldErrors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="container">
        <div>
            <h2><?= $id ? 'Editar Producto' : 'Nuevo Producto' ?></h2>
            <?= $crud->renderForm($id) ?>
        </div>
        <div>
            <h2>Lista de Productos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Categoría</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if ($product['image']): ?>
                                <img src="<?= htmlspecialchars($product['image']) ?>" class="product-img" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                <span style="color: #999;">Sin imagen</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($product['name']) ?></td>
                        <td>$<?= number_format($product['price'], 2) ?></td>
                        <td><?= htmlspecialchars($product['category'] ?? 'N/A') ?></td>
                        <td><a href="?id=<?= $product['id'] ?>">Editar</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p><a href="products.php">← Crear nuevo producto</a></p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
