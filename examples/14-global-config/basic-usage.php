<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\GlobalMetadata;

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create GlobalMetadata instance
$config = new GlobalMetadata($pdo);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Config - Basic Usage</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        h1 { color: #667eea; }
        .section { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 8px; }
        pre { background: #2d3748; color: #fff; padding: 15px; border-radius: 6px; overflow-x: auto; }
        .success { color: #48bb78; }
        .info { color: #4299e1; }
        button { background: #667eea; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
        button:hover { background: #5568d3; }
    </style>
</head>
<body>
    <h1>🌍 Global Config - Basic Usage</h1>

    <?php
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'set_app':
                    $config->set('application', [
                        'name' => $_POST['app_name'],
                        'version' => $_POST['app_version'],
                        'company' => $_POST['app_company']
                    ]);
                    echo '<div class="section success">✅ Application config saved!</div>';
                    break;

                case 'set_theme':
                    $config->set('theme', [
                        'primary_color' => $_POST['primary_color'],
                        'secondary_color' => $_POST['secondary_color'],
                        'dark_mode' => isset($_POST['dark_mode'])
                    ]);
                    echo '<div class="section success">✅ Theme config saved!</div>';
                    break;

                case 'clear':
                    $config->clear();
                    echo '<div class="section success">✅ All config cleared!</div>';
                    break;
            }
        }
    }

    // Get current config
    $app = $config->get('application', [
        'name' => 'My Application',
        'version' => '1.0.0',
        'company' => 'My Company'
    ]);

    $theme = $config->get('theme', [
        'primary_color' => '#667eea',
        'secondary_color' => '#764ba2',
        'dark_mode' => false
    ]);
    ?>

    <div class="section">
        <h2>📝 Application Settings</h2>
        <form method="POST">
            <input type="hidden" name="action" value="set_app">
            <p>
                <label>Application Name:</label><br>
                <input type="text" name="app_name" value="<?= htmlspecialchars($app['name']) ?>" style="width: 100%; padding: 8px;">
            </p>
            <p>
                <label>Version:</label><br>
                <input type="text" name="app_version" value="<?= htmlspecialchars($app['version']) ?>" style="width: 100%; padding: 8px;">
            </p>
            <p>
                <label>Company:</label><br>
                <input type="text" name="app_company" value="<?= htmlspecialchars($app['company']) ?>" style="width: 100%; padding: 8px;">
            </p>
            <button type="submit">Save Application Settings</button>
        </form>
    </div>

    <div class="section">
        <h2>🎨 Theme Settings</h2>
        <form method="POST">
            <input type="hidden" name="action" value="set_theme">
            <p>
                <label>Primary Color:</label><br>
                <input type="color" name="primary_color" value="<?= htmlspecialchars($theme['primary_color']) ?>">
                <span><?= htmlspecialchars($theme['primary_color']) ?></span>
            </p>
            <p>
                <label>Secondary Color:</label><br>
                <input type="color" name="secondary_color" value="<?= htmlspecialchars($theme['secondary_color']) ?>">
                <span><?= htmlspecialchars($theme['secondary_color']) ?></span>
            </p>
            <p>
                <label>
                    <input type="checkbox" name="dark_mode" <?= $theme['dark_mode'] ? 'checked' : '' ?>>
                    Enable Dark Mode
                </label>
            </p>
            <button type="submit">Save Theme Settings</button>
        </form>
    </div>

    <div class="section">
        <h2>📋 Current Configuration</h2>
        <pre><?= json_encode($config->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></pre>
    </div>

    <div class="section">
        <h2>🗑️ Clear All Config</h2>
        <form method="POST" onsubmit="return confirm('Are you sure you want to clear all configuration?')">
            <input type="hidden" name="action" value="clear">
            <button type="submit" style="background: #e53e3e;">Clear All Configuration</button>
        </form>
    </div>

    <div class="section info">
        <h3>💡 CLI Usage</h3>
        <pre>
# Set configuration
php bin/dynamiccrud config:set application.name "My App"

# Get configuration
php bin/dynamiccrud config:get application.name

# List all
php bin/dynamiccrud config:list

# Delete key
php bin/dynamiccrud config:delete old.key
        </pre>
    </div>
</body>
</html>
