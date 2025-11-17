<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$table = $_GET['table'] ?? $_POST['table'] ?? 'users';
$crud = new Morpheus($pdo, $table);

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $csv = file_get_contents($_FILES['csv_file']['tmp_name']);
    $preview = isset($_POST['preview']);
    
    $result = $crud->import($csv, [
        'preview' => $preview,
        'skip_errors' => isset($_POST['skip_errors'])
    ]);
}

if (isset($_GET['template'])) {
    $template = $crud->generateImportTemplate();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $table . '_template.csv"');
    echo $template;
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Import Data - DynamicCRUD</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 50px auto; padding: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-warning { background: #fff3cd; color: #856404; }
        .alert-danger { background: #f8d7da; color: #721c24; }
        .info-box { background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin: 5px; border: none; cursor: pointer; }
        .btn:hover { background: #5568d3; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>📥 Import Data</h1>
    
    <div class="info-box">
        <strong>Table:</strong> <?= htmlspecialchars($table) ?><br>
        <a href="?table=<?= $table ?>&template=1" class="btn">⬇️ Download Template</a>
    </div>
    
    <?php if ($result): ?>
        <?php if (isset($result['details']) && $result['details'][0]['status'] === 'preview'): ?>
            <div class="alert alert-warning">
                <strong>Preview Mode</strong><br>
                Rows to import: <?= count($result['details']) ?><br>
                No data was imported. Remove preview checkbox to import.
            </div>
        <?php else: ?>
            <div class="alert <?= $result['errors'] > 0 ? 'alert-danger' : 'alert-success' ?>">
                <strong>Import Results:</strong><br>
                ✅ Success: <?= $result['success'] ?><br>
                ❌ Errors: <?= $result['errors'] ?><br>
                ⏭️ Skipped: <?= $result['skipped'] ?>
            </div>
            
            <?php if ($result['errors'] > 0): ?>
                <h3>Errors:</h3>
                <ul>
                    <?php foreach ($result['details'] as $detail): ?>
                        <?php if (in_array($detail['status'], ['error', 'validation_error'])): ?>
                            <li>Row <?= $detail['row'] ?>: <?= htmlspecialchars($detail['message'] ?? json_encode($detail['errors'])) ?></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="table" value="<?= htmlspecialchars($table) ?>">
        
        <div class="form-group">
            <label>CSV File:</label>
            <input type="file" name="csv_file" accept=".csv" required>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="preview" value="1"> Preview only (don't import)
            </label>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="skip_errors" value="1"> Skip errors and continue
            </label>
        </div>
        
        <button type="submit" class="btn">📥 Import</button>
        <a href="export.php?table=<?= $table ?>" class="btn">📤 Export</a>
    </form>
</body>
</html>
