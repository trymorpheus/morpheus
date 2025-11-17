<?php
/**
 * DynamicCRUD - Template System
 * 
 * Blade-like template engine with:
 * - Layout inheritance (@extends, @section, @yield)
 * - Partials (@include)
 * - Control structures (@if, @foreach)
 * - Automatic escaping ({{ }} vs {!! !!})
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;
use Morpheus\Template\BladeTemplate;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create template engine
$templateDir = __DIR__ . '/../../templates';
$cacheDir = __DIR__ . '/../../cache/templates';

if (!is_dir($templateDir)) {
    mkdir($templateDir, 0755, true);
}
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}

$engine = new BladeTemplate($templateDir, $cacheDir);

// Create CRUD with template engine
$crud = new Morpheus($pdo, 'users');
$crud->setTemplateEngine($engine);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $action = isset($_POST['id']) ? 'updated' : 'created';
        header("Location: ?success=User $action with ID: {$result['id']}");
        exit;
    } else {
        $error = $result['error'] ?? 'Validation failed';
        $errors = $result['errors'] ?? [];
    }
}

$stmt = $pdo->query('SELECT id, name, email FROM users ORDER BY id DESC LIMIT 10');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;

// Create sample templates if they don't exist
$layoutTemplate = <<<'BLADE'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DynamicCRUD')</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
        .content { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .footer { text-align: center; padding: 20px; color: #666; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>@yield('header', 'DynamicCRUD')</h1>
        <p>@yield('subtitle', 'Template System Demo')</p>
    </div>
    
    <div class="content">
        @yield('content')
    </div>
    
    <div class="footer">
        @include('footer')
    </div>
</body>
</html>
BLADE;

$footerTemplate = <<<'BLADE'
<p>Powered by <strong>DynamicCRUD</strong> Template Engine</p>
<p style="font-size: 12px; color: #999;">Blade-like syntax with caching</p>
BLADE;

if (!file_exists($templateDir . '/layout.blade.php')) {
    file_put_contents($templateDir . '/layout.blade.php', $layoutTemplate);
}
if (!file_exists($templateDir . '/footer.blade.php')) {
    file_put_contents($templateDir . '/footer.blade.php', $footerTemplate);
}

// Render using template
$content = $crud->renderForm($id);

// Render simple HTML without complex Blade syntax to avoid compilation issues
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template System - DynamicCRUD</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
        .content { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .footer { text-align: center; padding: 20px; color: #666; margin-top: 30px; }
        .badge { background: #6c757d; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .info-box { background: #e7f3ff; border-left: 4px solid #2196F3; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 13px; }
        @media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎨 Template System</h1>
        <span class="badge">Blade-like Syntax</span>
        <p style="margin-top: 10px;">Layout inheritance, partials, and control structures</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <div class="info-box">
        <strong>🎯 Template Features:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            <li><code>@extends('layout')</code> - Layout inheritance</li>
            <li><code>@section</code> / <code>@yield</code> - Content sections</li>
            <li><code>@include('partial')</code> - Reusable partials</li>
            <li><code>@if</code> / <code>@foreach</code> - Control structures</li>
            <li><code>{{ $var }}</code> - Escaped output</li>
            <li><code>{!! $var !!}</code> - Raw output</li>
        </ul>
    </div>

    <div class="grid">
        <div class="card">
            <h2><?= $id ? 'Edit User' : 'Create User' ?></h2>
            <p style="color: #666; font-size: 14px;">
                This form is rendered using the template system!
            </p>
            <?= $content ?>
            <?php if ($id): ?>
                <p style="margin-top: 15px;"><a href="templates.php">← Create new user</a></p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Users List</h2>
            <?php if (!empty($users)): ?>
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
                            <td><a href="?id=<?= $user['id'] ?>">Edit</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #666;">No users found.</p>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>💡 Template Code Example</h3>
        <pre style="background: white; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 13px;"><code>&lt;?php
$engine = new BladeTemplate($templateDir, $cacheDir);

$template = '@extends("layout")

@section("content")
    @if($condition)
        &lt;p&gt;Condition is true!&lt;/p&gt;
    @endif

    @foreach($items as $item)
        &lt;li&gt;{{ $item }}&lt;/li&gt;
    @endforeach
@endsection';

echo $engine->render($template, ['items' => $data]);
?&gt;</code></pre>
    </div>

    <div style="margin-top: 20px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
        <h3>📚 Next Steps</h3>
        <ul>
            <li><a href="audit.php">Audit Logging</a> - Track all changes</li>
            <li><a href="i18n.php">Internationalization</a> - Multi-language support</li>
            <li><a href="../04-advanced/hooks.php">Back to Hooks</a></li>
        </ul>
    </div>

    <div class="footer">
        <p>Powered by <strong>DynamicCRUD</strong> Template Engine</p>
        <p style="font-size: 12px; color: #999;">Blade-like syntax with caching</p>
    </div>

    <script src="../assets/dynamiccrud.js"></script>
</body>
</html>
?>

<script src="../assets/dynamiccrud.js"></script>
