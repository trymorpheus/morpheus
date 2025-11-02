<?php

require __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;
use DynamicCRUD\Template\BladeTemplate;

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create template engine
$templateEngine = new BladeTemplate(
    __DIR__ . '/../templates',
    __DIR__ . '/../cache/templates'
);

// Create CRUD with template engine
$crud = new DynamicCRUD($pdo, 'users');
$crud->setTemplateEngine($templateEngine);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template System Demo</title>
    <link rel="stylesheet" href="assets/dynamiccrud.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .demo-header {
            background: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .demo-header h1 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .demo-header p {
            margin: 0;
            color: #666;
        }
        .template-example {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .template-example h3 {
            margin-top: 0;
            color: #333;
        }
        .template-code {
            background: #f8f8f8;
            padding: 15px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="demo-header">
        <h1>🎨 Template System Demo</h1>
        <p>DynamicCRUD with Blade-like template engine</p>
    </div>

    <div class="template-example">
        <h3>Example 1: Simple Variable</h3>
        <div class="template-code">
            <?php
            $template = 'Hello, {{ $name }}!';
            echo htmlspecialchars($template) . '<br><br>';
            echo '<strong>Output:</strong> ' . $templateEngine->render($template, ['name' => 'Mario']);
            ?>
        </div>
    </div>

    <div class="template-example">
        <h3>Example 2: Conditional</h3>
        <div class="template-code">
            <?php
            $template = '@if ($age >= 18)
You are an adult
@else
You are a minor
@endif';
            echo '<pre>' . htmlspecialchars($template) . '</pre>';
            echo '<strong>Output (age=25):</strong> ' . $templateEngine->render($template, ['age' => 25]) . '<br>';
            echo '<strong>Output (age=15):</strong> ' . $templateEngine->render($template, ['age' => 15]);
            ?>
        </div>
    </div>

    <div class="template-example">
        <h3>Example 3: Loop</h3>
        <div class="template-code">
            <?php
            $template = '<ul>
@foreach ($items as $item)
    <li>{{ $item }}</li>
@endforeach
</ul>';
            echo '<pre>' . htmlspecialchars($template) . '</pre>';
            echo '<strong>Output:</strong><br>';
            echo $templateEngine->render($template, ['items' => ['Apple', 'Banana', 'Orange']]);
            ?>
        </div>
    </div>

    <div class="template-example">
        <h3>Example 4: Raw HTML</h3>
        <div class="template-code">
            <?php
            $template = 'Escaped: {{ $html }}<br>Raw: {!! $html !!}';
            echo htmlspecialchars($template) . '<br><br>';
            echo '<strong>Output:</strong><br>' . $templateEngine->render($template, ['html' => '<strong>Bold</strong>']);
            ?>
        </div>
    </div>

    <div class="template-example">
        <h3>Example 5: Form with Template</h3>
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $crud->handleSubmission();
            if ($result['success']) {
                echo '<div class="alert alert-success">✅ User saved successfully! ID: ' . $result['id'] . '</div>';
            } else {
                echo '<div class="alert alert-error">❌ Error: ' . ($result['error'] ?? 'Validation failed') . '</div>';
            }
        }
        
        echo $crud->renderForm($_GET['id'] ?? null);
        ?>
    </div>

    <script src="assets/dynamiccrud.js"></script>
</body>
</html>
