<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;
use Morpheus\GlobalMetadata;

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Configure theme (if not already set)
$config = new GlobalMetadata($pdo);
if (!$config->has('theme')) {
    $config->set('theme', [
        'primary_color' => '#667eea',
        'secondary_color' => '#764ba2',
        'background_color' => '#ffffff',
        'text_color' => '#333333',
        'font_family' => 'Inter, system-ui, sans-serif',
        'border_radius' => '8px'
    ]);
}

if (!$config->has('application')) {
    $config->set('application', [
        'name' => 'DynamicCRUD Demo',
        'version' => '2.9.0',
        'company' => 'Your Company'
    ]);
}

// Create CRUD with global config enabled
$crud = new Morpheus($pdo, 'users');
$crud->enableGlobalConfig(); // Enable theme integration!

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        header('Location: ?success=1');
        exit;
    } else {
        $error = $result['error'] ?? 'Error al guardar';
    }
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎨 Theme Integration</title>
    <style>
        body { font-family: var(--font-family, system-ui, sans-serif); background: #f5f5f5; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: linear-gradient(135deg, var(--primary-color, #667eea) 0%, var(--secondary-color, #764ba2) 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 32px; }
        .header p { margin: 10px 0 0; opacity: 0.9; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .actions { margin-bottom: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background: var(--primary-color, #667eea); color: white; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .btn:hover { opacity: 0.9; }
        .theme-config { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .theme-config h3 { margin-top: 0; }
        .color-preview { display: inline-block; width: 30px; height: 30px; border-radius: 4px; vertical-align: middle; margin-left: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎨 Theme Integration</h1>
            <p>Formularios con tema personalizado desde Global Config</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success">
                ✅ Usuario guardado correctamente!
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="error">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="theme-config">
            <h3>📋 Configuración Actual del Tema</h3>
            <?php
            $theme = $config->get('theme', []);
            $app = $config->get('application', []);
            ?>
            <p><strong>Aplicación:</strong> <?= htmlspecialchars($app['name'] ?? 'N/A') ?> v<?= htmlspecialchars($app['version'] ?? 'N/A') ?></p>
            <p><strong>Color Primario:</strong> <?= htmlspecialchars($theme['primary_color'] ?? '#667eea') ?> <span class="color-preview" style="background: <?= htmlspecialchars($theme['primary_color'] ?? '#667eea') ?>"></span></p>
            <p><strong>Color Secundario:</strong> <?= htmlspecialchars($theme['secondary_color'] ?? '#764ba2') ?> <span class="color-preview" style="background: <?= htmlspecialchars($theme['secondary_color'] ?? '#764ba2') ?>"></span></p>
            <p><strong>Fuente:</strong> <?= htmlspecialchars($theme['font_family'] ?? 'system-ui, sans-serif') ?></p>
            <p><a href="../14-global-config/basic-usage.php" class="btn">⚙️ Configurar Tema</a></p>
        </div>

        <div class="actions">
            <?php if ($id): ?>
                <a href="?" class="btn">← Volver</a>
            <?php else: ?>
                <a href="?id=new" class="btn">➕ Nuevo Usuario</a>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['id'])): ?>
            <!-- Form with theme applied -->
            <?= $crud->renderForm($id === 'new' ? null : $id) ?>
        <?php else: ?>
            <!-- List -->
            <?php
            $users = $pdo->query("SELECT * FROM users ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <div style="background: white; padding: 20px; border-radius: 8px;">
                <h3>👥 Usuarios Recientes</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #ddd;">
                            <th style="padding: 10px; text-align: left;">ID</th>
                            <th style="padding: 10px; text-align: left;">Nombre</th>
                            <th style="padding: 10px; text-align: left;">Email</th>
                            <th style="padding: 10px; text-align: left;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px;"><?= $user['id'] ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($user['name']) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($user['email']) ?></td>
                                <td style="padding: 10px;">
                                    <a href="?id=<?= $user['id'] ?>" class="btn" style="padding: 6px 12px; font-size: 14px;">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
