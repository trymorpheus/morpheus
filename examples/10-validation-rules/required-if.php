<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'vr_products');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        header('Location: ?success=1');
        exit;
    }
}

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Required If - Validation Rules</title>
    <link rel="stylesheet" href="../assets/dynamiccrud.css">
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info-box { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin-bottom: 20px; }
        h1 { color: #333; }
        .back-link { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <h1>⚠️ Required If Validation</h1>
    <p><em>Tabla: vr_products</em></p>
    
    <div class="info-box">
        <strong>Regla:</strong> El campo <code>min_stock</code> es obligatorio si <code>status = "active"</code>.<br>
        <strong>Ejemplo:</strong> Los productos activos deben tener un stock mínimo definido.
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✅ Producto guardado correctamente</div>
    <?php endif; ?>
    
    <?php if (isset($result) && !$result['success']): ?>
        <div class="alert alert-danger">
            <?php if (isset($result['error'])): ?>
                ❌ Error: <?= htmlspecialchars($result['error']) ?>
            <?php elseif (isset($result['errors'])): ?>
                ❌ Errores de validación:
            <?php endif; ?>
            <?php if (isset($result['errors'])): ?>
                <ul>
                    <?php foreach ($result['errors'] as $field => $error): ?>
                        <li><strong><?= htmlspecialchars($field) ?>:</strong> <?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!$id): ?>
        <h2>Productos Existentes</h2>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="padding: 10px; border: 1px solid #ddd;">ID</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Nombre</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Status</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Min Stock</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT * FROM vr_products ORDER BY id DESC");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?= $row['id'] ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($row['name']) ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($row['status']) ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><?= $row['min_stock'] ?? 'N/A' ?></td>
                    <td style="padding: 10px; border: 1px solid #ddd;">
                        <a href="?id=<?= $row['id'] ?>" style="color: #007bff; text-decoration: none;">✏️ Editar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <h2><?= $id ? 'Editar Producto' : 'Crear Producto' ?></h2>
    <?= $crud->renderForm($id) ?>
    
    <a href="required-if.php" class="back-link">← Volver al listado</a> |
    <a href="unique-together.php" class="back-link">Ejemplo anterior</a> |
    <a href="conditional.php" class="back-link">Siguiente ejemplo →</a>
</body>
</html>
