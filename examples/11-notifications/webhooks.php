<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'notif_contacts');

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
    <title>Webhooks - DynamicCRUD</title>
    <link rel="stylesheet" href="../assets/dynamiccrud.css">
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; padding: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info-box { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin-bottom: 20px; }
        h1 { color: #333; }
        .back-link { display: inline-block; margin-top: 20px; color: #007bff; text-decoration: none; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
    <h1>📧 Webhooks</h1>
    <p><em>Tabla: notif_contacts</em></p>
    
    <div class="info-box">
        <strong>Configuración:</strong><br>
        • Webhook POST a <code>https://webhook.site/unique-id</code> al crear contacto<br>
        • Header de autorización incluido<br>
        • Payload JSON con datos del contacto<br>
        <br>
        <strong>Nota:</strong> Reemplaza <code>unique-id</code> en setup.sql con tu URL de <a href="https://webhook.site" target="_blank">webhook.site</a>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            ✅ Contacto guardado correctamente<br>
            🔗 Webhook disparado (revisa webhook.site)
        </div>
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
        <h2>Contactos Existentes</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Status</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT * FROM notif_contacts ORDER BY id DESC");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['status']) ?></td>
                    <td>
                        <a href="?id=<?= $row['id'] ?>" style="color: #007bff; text-decoration: none;">✏️ Editar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <h2><?= $id ? 'Editar Contacto' : 'Crear Contacto' ?></h2>
    <?= $crud->renderForm($id) ?>
    
    <a href="webhooks.php" class="back-link">← Volver al listado</a> |
    <a href="email-notifications.php" class="back-link">← Ejemplo anterior</a>
</body>
</html>
