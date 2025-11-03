<?php
/**
 * DynamicCRUD - Table Metadata: Dynamic Forms
 * 
 * Demonstrates tabbed forms using table metadata
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use DynamicCRUD\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new DynamicCRUD($pdo, 'contacts');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $action = isset($_POST['id']) ? 'updated' : 'created';
        header("Location: ?success=Contact $action with ID: {$result['id']}");
        exit;
    } else {
        $error = $result['error'] ?? 'Validation failed';
        $errors = $result['errors'] ?? [];
    }
}

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Forms - DynamicCRUD</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 20px; background: #f5f5f5; }
        .badge { background: #6f42c1; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
        .info-box { background: #f3e5f5; border-left: 4px solid #9c27b0; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .card { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        /* Tab styles */
        .form-tabs { margin-bottom: 20px; }
        .tab-nav { display: flex; gap: 5px; border-bottom: 2px solid #dee2e6; margin-bottom: 20px; }
        .tab-button { padding: 12px 24px; background: none; border: none; border-bottom: 3px solid transparent; cursor: pointer; font-size: 14px; font-weight: 500; color: #666; transition: all 0.2s; }
        .tab-button:hover { color: #333; background: #f8f9fa; }
        .tab-button.active { color: #667eea; border-bottom-color: #667eea; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 13px; }
    </style>
</head>
<body>
    <h1>📑 Dynamic Forms - Tabs</h1>
    <span class="badge">Phase 1</span>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <div class="info-box">
        <strong>🎯 Tabbed Form Layout:</strong>
        <p style="margin: 10px 0 0 0;">
            Forms can be organized into tabs for better UX. Click between tabs to see different field groups.
        </p>
    </div>

    <div class="card">
        <h2><?= $id ? 'Edit Contact' : 'New Contact' ?></h2>
        <?= $crud->renderForm($id) ?>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>💡 Configure Tabbed Forms</h3>
        <pre style="background: white; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>ALTER TABLE contacts COMMENT = '{
  "display_name": "Contacts",
  "form": {
    "layout": "tabs",
    "tabs": [
      {
        "name": "basic",
        "label": "Basic Info",
        "fields": ["name", "email"]
      },
      {
        "name": "contact",
        "label": "Contact Details",
        "fields": ["phone", "website"]
      },
      {
        "name": "message",
        "label": "Message",
        "fields": ["message"]
      }
    ]
  }
}';</code></pre>
    </div>

    <div style="margin-top: 20px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
        <h3>📚 Next Steps</h3>
        <ul>
            <li><a href="ui-customization.php">UI Customization</a> - List views and search</li>
            <li><a href="../01-basic/">Back to Basic Example</a></li>
        </ul>
    </div>

    <script src="../assets/dynamiccrud.js"></script>
</body>
</html>
