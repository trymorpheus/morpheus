<?php
/**
 * Advanced Input Types Demo
 * 
 * Demonstrates new HTML5 input types and metadata options:
 * - color, tel, password, search, time, week, month, range
 * - placeholder, pattern, step, readonly, autocomplete
 */

require_once __DIR__ . '/../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

// Database connection
$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create demo table if not exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS advanced_demo (
        id INT PRIMARY KEY AUTO_INCREMENT,
        brand_color VARCHAR(7) COMMENT '{\"type\": \"color\", \"label\": \"Brand Color\", \"placeholder\": \"#000000\"}',
        phone VARCHAR(20) COMMENT '{\"type\": \"tel\", \"label\": \"Phone Number\", \"placeholder\": \"555-123-4567\", \"pattern\": \"[0-9]{3}-[0-9]{3}-[0-9]{4}\"}',
        password VARCHAR(255) COMMENT '{\"type\": \"password\", \"label\": \"Password\", \"minlength\": 8, \"placeholder\": \"Min 8 characters\"}',
        search_query VARCHAR(255) COMMENT '{\"type\": \"search\", \"label\": \"Search\", \"placeholder\": \"Search...\"}',
        appointment_time TIME COMMENT '{\"type\": \"time\", \"label\": \"Appointment Time\"}',
        birth_week VARCHAR(10) COMMENT '{\"type\": \"week\", \"label\": \"Birth Week\"}',
        birth_month VARCHAR(7) COMMENT '{\"type\": \"month\", \"label\": \"Birth Month\"}',
        satisfaction INT COMMENT '{\"type\": \"range\", \"label\": \"Satisfaction Level\", \"min\": 0, \"max\": 100, \"step\": 10}',
        email VARCHAR(255) COMMENT '{\"type\": \"email\", \"label\": \"Email\", \"placeholder\": \"user@example.com\", \"autocomplete\": \"email\"}',
        website VARCHAR(255) COMMENT '{\"type\": \"url\", \"label\": \"Website\", \"placeholder\": \"https://example.com\"}',
        notes TEXT COMMENT '{\"label\": \"Notes\", \"placeholder\": \"Enter your notes here...\"}',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '{\"readonly\": true, \"label\": \"Created At\"}'
    )
");

$crud = new DynamicCRUD($pdo, 'advanced_demo');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        header('Location: ?success=1&id=' . $result['id']);
        exit;
    } else {
        $error = $result['error'] ?? 'Validation errors';
        $errors = $result['errors'] ?? [];
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $crud->delete((int)$_GET['delete']);
    header('Location: ?deleted=1');
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Input Types - DynamicCRUD</title>
    <link rel="stylesheet" href="assets/dynamiccrud.css">
    <style>
        .demo-info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
        }
        .demo-info h3 {
            margin-top: 0;
            color: #1976d2;
        }
        .demo-info ul {
            margin: 10px 0;
        }
        .demo-info code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎨 Advanced Input Types Demo</h1>
        
        <div class="demo-info">
            <h3>New Features Demonstrated:</h3>
            <ul>
                <li><code>type: "color"</code> - Color picker</li>
                <li><code>type: "tel"</code> - Phone number with pattern validation</li>
                <li><code>type: "password"</code> - Password field</li>
                <li><code>type: "search"</code> - Search input</li>
                <li><code>type: "time"</code> - Time picker</li>
                <li><code>type: "week"</code> - Week picker</li>
                <li><code>type: "month"</code> - Month picker</li>
                <li><code>type: "range"</code> - Slider input</li>
                <li><code>placeholder</code> - Placeholder text</li>
                <li><code>pattern</code> - Regex validation</li>
                <li><code>step</code> - Increment step</li>
                <li><code>readonly</code> - Read-only fields</li>
                <li><code>autocomplete</code> - Browser autocomplete hints</li>
            </ul>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                ✓ Record saved successfully! ID: <?= htmlspecialchars($_GET['id']) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">
                ✓ Record deleted successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error">
                ✗ <?= htmlspecialchars($error) ?>
                <?php if (!empty($errors)): ?>
                    <ul>
                        <?php foreach ($errors as $field => $message): ?>
                            <li><?= htmlspecialchars($field) ?>: <?= htmlspecialchars($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <h2><?= isset($_GET['id']) ? 'Edit Record' : 'Create New Record' ?></h2>
        
        <?= $crud->renderForm($_GET['id'] ?? null) ?>

        <hr>

        <h2>📋 Records List</h2>
        <?php
        $list = $crud->list(['perPage' => 10]);
        if (!empty($list['data'])):
        ?>
            <table class="crud-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Brand Color</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Satisfaction</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list['data'] as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td>
                                <span style="display: inline-block; width: 30px; height: 20px; background: <?= htmlspecialchars($row['brand_color'] ?? '#000') ?>; border: 1px solid #ccc;"></span>
                                <?= htmlspecialchars($row['brand_color'] ?? '') ?>
                            </td>
                            <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($row['satisfaction'] ?? '0') ?>%</td>
                            <td>
                                <a href="?id=<?= $row['id'] ?>">Edit</a> |
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this record?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No records found. Create one above!</p>
        <?php endif; ?>

        <p><a href="index.php">← Back to Examples</a></p>
    </div>

    <script src="assets/dynamiccrud.js"></script>
</body>
</html>
