<?php
/**
 * DynamicCRUD - Advanced Input Types
 * 
 * Demonstrates HTML5 input types and metadata options:
 * color, tel, password, search, time, week, month, range, etc.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'advanced_inputs');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        header("Location: ?success=Record saved with ID: {$result['id']}");
        exit;
    } else {
        $error = $result['error'] ?? 'Validation failed';
        $errors = $result['errors'] ?? [];
    }
}

$stmt = $pdo->query('SELECT * FROM advanced_inputs ORDER BY id DESC LIMIT 10');
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Inputs - DynamicCRUD</title>
    <link rel="stylesheet" href="../assets/dynamiccrud.css">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .badge { background: #e83e8c; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .info-box { background: #f8d7da; border-left: 4px solid #e83e8c; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .color-preview { display: inline-block; width: 30px; height: 20px; border: 1px solid #ccc; border-radius: 3px; vertical-align: middle; margin-right: 8px; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 13px; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <h1>🎨 Advanced Input Types</h1>
    <span class="badge">HTML5 Inputs</span>
    <p style="color: #666; margin: 10px 0 20px 0;">
        Explore all HTML5 input types supported by DynamicCRUD.
    </p>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <div class="info-box">
        <strong>🎯 Input Types Demonstrated:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            <li><code>color</code> - Color picker</li>
            <li><code>tel</code> - Phone with pattern validation</li>
            <li><code>time</code>, <code>week</code>, <code>month</code> - Date/time pickers</li>
            <li><code>range</code> - Slider input</li>
            <li><code>search</code> - Search field</li>
            <li><code>password</code> - Password field</li>
        </ul>
    </div>

    <div class="container">
        <div class="card">
            <h2><?= $id ? 'Edit Record' : 'Create Record' ?></h2>
            <p style="color: #666; font-size: 14px;">
                Try all the different input types!
            </p>
            <?= $crud->renderForm($id) ?>
        </div>

        <div class="card">
            <h2>Records</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Color</th>
                        <th>Phone</th>
                        <th>Satisfaction</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                    <tr>
                        <td><?= htmlspecialchars($record['id']) ?></td>
                        <td>
                            <span class="color-preview" style="background: <?= htmlspecialchars($record['brand_color'] ?? '#000') ?>"></span>
                            <?= htmlspecialchars($record['brand_color'] ?? 'N/A') ?>
                        </td>
                        <td><?= htmlspecialchars($record['phone'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($record['satisfaction'] ?? '0') ?>%</td>
                        <td><a href="?id=<?= $record['id'] ?>">Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p style="margin-top: 15px;"><a href="advanced-inputs.php">← Create new record</a></p>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
        <h3>📚 Next Steps</h3>
        <ul>
            <li><a href="../04-advanced/hooks.php">Hooks System</a> - Add custom logic</li>
            <li><a href="metadata.php">Back to Metadata</a></li>
        </ul>
    </div>
    <script src="../assets/dynamiccrud.js"></script>
</body>
</html>
