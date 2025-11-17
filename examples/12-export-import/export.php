<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$table = $_GET['table'] ?? 'users';
$crud = new Morpheus($pdo, $table);

if (isset($_GET['download'])) {
    $crud->downloadExport("{$table}_export.csv");
}

$csv = $crud->export('csv');
$lines = substr_count($csv, "\n") - 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Export Data - DynamicCRUD</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; padding: 20px; }
        .info-box { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin-bottom: 20px; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin: 5px; }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <h1>📤 Export Data</h1>
    
    <div class="info-box">
        <strong>Table:</strong> <?= htmlspecialchars($table) ?><br>
        <strong>Rows:</strong> <?= $lines ?>
    </div>
    
    <a href="?table=<?= $table ?>&download=1" class="btn">⬇️ Download CSV</a>
    <a href="import.php?table=<?= $table ?>" class="btn">📥 Import Data</a>
    
    <h2>Preview</h2>
    <pre><?= htmlspecialchars(substr($csv, 0, 1000)) ?><?= strlen($csv) > 1000 ? '...' : '' ?></pre>
</body>
</html>
