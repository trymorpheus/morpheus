<?php
/**
 * DynamicCRUD - Internationalization (i18n)
 * 
 * Multi-language support with auto-detection.
 * Supports: English (en), Spanish (es), French (fr)
 * Detection order: URL param > Session > Browser Accept-Language
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Auto-detect language or force specific locale
$locale = $_GET['lang'] ?? null;
$crud = new Morpheus($pdo, 'users', locale: $locale);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $action = isset($_POST['id']) ? 'updated' : 'created';
        $lang = $_GET['lang'] ?? 'en';
        header("Location: ?lang=$lang&success=User $action with ID: {$result['id']}");
        exit;
    } else {
        $error = $result['error'] ?? 'Validation failed';
        $errors = $result['errors'] ?? [];
    }
}

$stmt = $pdo->query('SELECT id, name, email FROM users ORDER BY id DESC LIMIT 10');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
$currentLang = $_GET['lang'] ?? 'en';
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internationalization - DynamicCRUD</title>
    <link rel="stylesheet" href="../assets/dynamiccrud.css">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .badge { background: #007bff; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
        .lang-switcher { display: flex; gap: 10px; margin-bottom: 20px; }
        .lang-switcher a { padding: 8px 16px; background: #f8f9fa; border-radius: 4px; text-decoration: none; color: #333; }
        .lang-switcher a.active { background: #007bff; color: white; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .info-box { background: #cfe2ff; border-left: 4px solid #0d6efd; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <h1>🌍 Internationalization (i18n)</h1>
    <span class="badge">3 Languages</span>
    
    <div class="lang-switcher">
        <a href="?lang=en" class="<?= $currentLang === 'en' ? 'active' : '' ?>">🇬🇧 English</a>
        <a href="?lang=es" class="<?= $currentLang === 'es' ? 'active' : '' ?>">🇪🇸 Español</a>
        <a href="?lang=fr" class="<?= $currentLang === 'fr' ? 'active' : '' ?>">🇫🇷 Français</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <div class="info-box">
        <strong>🎯 How it works:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            <li><strong>Auto-detection:</strong> URL param (?lang=es) → Session → Browser Accept-Language</li>
            <li><strong>Form labels:</strong> Automatically translated</li>
            <li><strong>Validation messages:</strong> Localized error messages</li>
            <li><strong>Client-side:</strong> JavaScript validation also translated</li>
        </ul>
    </div>

    <div class="container">
        <div class="card">
            <h2><?= $id ? 'Edit User' : 'Create User' ?></h2>
            <p style="color: #666; font-size: 14px;">
                Notice how the form, labels, and validation messages change with language!
            </p>
            <?= $crud->renderForm($id) ?>
        </div>

        <div class="card">
            <h2>Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><a href="?lang=<?= $currentLang ?>&id=<?= $user['id'] ?>">Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p style="margin-top: 15px;"><a href="?lang=<?= $currentLang ?>">← Create new user</a></p>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>💡 Usage</h3>
        <pre style="background: white; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>// Auto-detect language
$crud = new Morpheus($pdo, 'users');

// Force specific language
$crud = new Morpheus($pdo, 'users', locale: 'es');

// Or set via URL
// ?lang=es (Spanish)
// ?lang=fr (French)
// ?lang=en (English)</code></pre>
    </div>

    <div style="margin-top: 20px; padding: 20px; background: #fff3cd; border-radius: 8px;">
        <h3>📋 Supported Languages</h3>
        <ul>
            <li><strong>English (en)</strong> - Default language</li>
            <li><strong>Spanish (es)</strong> - Español completo</li>
            <li><strong>French (fr)</strong> - Français complet</li>
        </ul>
        <p><strong>Add more languages:</strong> Create JSON files in <code>src/I18n/locales/</code></p>
    </div>

    <div style="margin-top: 20px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
        <h3>📚 Next Steps</h3>
        <ul>
            <li><a href="templates.php?lang=<?= $currentLang ?>">Template System</a> - Custom layouts with Blade syntax</li>
            <li><a href="audit.php?lang=<?= $currentLang ?>">Audit Logging</a> - Track all changes</li>
            <li><a href="../04-advanced/hooks.php">Back to Hooks</a></li>
        </ul>
    </div>
    <script src="../assets/dynamiccrud.js"></script>
</body>
</html>
