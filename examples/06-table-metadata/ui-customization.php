<?php
/**
 * DynamicCRUD - Table Metadata: UI/UX Customization
 * 
 * Demonstrates table-level metadata for UI customization
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'users');
$metadata = $crud->getTableMetadata();

$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Metadata - DynamicCRUD</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1400px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        .badge { background: #17a2b8; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
        .info-box { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .list-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .list-header { padding-bottom: 20px; border-bottom: 2px solid #dee2e6; margin-bottom: 20px; }
        .list-search { margin-bottom: 20px; }
        .list-search input { padding: 8px; width: 300px; border: 1px solid #ddd; border-radius: 4px; }
        .list-search button { padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .list-table { width: 100%; border-collapse: collapse; }
        .list-table th, .list-table td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .list-table th { background: #f8f9fa; font-weight: 600; }
        .list-pagination { margin-top: 20px; text-align: center; }
        .list-pagination a { margin: 0 5px; padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        a { color: #007bff; text-decoration: none; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 13px; }
    </style>
</head>
<body>
    <h1>📊 Table Metadata - UI/UX Customization</h1>
    <span class="badge">Phase 1</span>

    <div class="info-box">
        <strong>🎯 Table Metadata Detected:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            <li><strong>Display Name:</strong> <?= htmlspecialchars($metadata->getDisplayName()) ?></li>
            <li><strong>Icon:</strong> <?= $metadata->getIcon() ? $metadata->getIcon() : 'None' ?></li>
            <li><strong>Per Page:</strong> <?= $metadata->getPerPage() ?></li>
            <li><strong>Default Sort:</strong> <?= htmlspecialchars($metadata->getDefaultSort()) ?></li>
        </ul>
    </div>

    <?= $crud->renderList(['search' => $search, 'page' => $page]) ?>

    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>💡 Add Table Metadata</h3>
        <pre style="background: white; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>ALTER TABLE users COMMENT = '{
  "display_name": "User Management",
  "icon": "Users",
  "description": "Complete user administration",
  "color": "#667eea",
  "list_view": {
    "columns": ["id", "name", "email", "created_at"],
    "default_sort": "created_at DESC",
    "per_page": 25,
    "searchable": ["name", "email"],
    "actions": ["edit", "delete"]
  }
}';

-- Or use HTML entities for icons:
-- "icon": "&#128101;"  (👥)
-- "icon": "&#128221;"  (📝)
-- "icon": "&#128722;"  (🛒)</code></pre>
    </div>
</body>
</html>
