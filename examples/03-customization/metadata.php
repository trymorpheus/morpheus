<?php
/**
 * DynamicCRUD - Metadata Customization
 * 
 * Control field behavior using JSON metadata in column comments.
 * 16+ options available: type, label, placeholder, min, max, pattern, etc.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'contacts');

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

$stmt = $pdo->query('SELECT id, name, email, phone, website FROM contacts ORDER BY id DESC LIMIT 10');
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metadata Customization - DynamicCRUD</title>
    <link rel="stylesheet" href="../assets/dynamiccrud.css">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .badge { background: #fd7e14; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .info-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .metadata-table { background: white; border-radius: 4px; overflow: hidden; }
        .metadata-table th { background: #495057; color: white; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <h1>⚙️ Metadata Customization</h1>
    <span class="badge">16+ Options</span>
    <p style="color: #666; margin: 10px 0 20px 0;">
        Control field behavior using JSON metadata in database column comments.
    </p>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <div class="info-box">
        <strong>🎯 Example Metadata:</strong>
        <pre style="background: white; padding: 10px; border-radius: 4px; margin-top: 10px; overflow-x: auto;"><code>ALTER TABLE contacts 
MODIFY COLUMN email VARCHAR(255) 
COMMENT '{"type": "email", "label": "Email Address", "tooltip": "We never share your email"}';</code></pre>
    </div>

    <div class="container">
        <div class="card">
            <h2><?= $id ? 'Edit Contact' : 'New Contact' ?></h2>
            <p style="color: #666; font-size: 14px;">
                Notice the specialized input types and validation!
            </p>
            <?= $crud->renderForm($id) ?>
        </div>

        <div class="card">
            <h2>Contacts List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?= htmlspecialchars($contact['name']) ?></td>
                        <td><?= htmlspecialchars($contact['email']) ?></td>
                        <td><?= htmlspecialchars($contact['phone'] ?? 'N/A') ?></td>
                        <td><a href="?id=<?= $contact['id'] ?>">Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if ($id): ?>
                <p style="margin-top: 15px;"><a href="metadata.php">← Create new contact</a></p>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>📋 Available Metadata Options</h3>
        <table class="metadata-table">
            <thead>
                <tr>
                    <th>Option</th>
                    <th>Type</th>
                    <th>Description</th>
                    <th>Example</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code>type</code></td>
                    <td>string</td>
                    <td>Input type</td>
                    <td>"email", "url", "tel", "color", "date"</td>
                </tr>
                <tr>
                    <td><code>label</code></td>
                    <td>string</td>
                    <td>Field label</td>
                    <td>"Your Email Address"</td>
                </tr>
                <tr>
                    <td><code>placeholder</code></td>
                    <td>string</td>
                    <td>Placeholder text</td>
                    <td>"Enter your name"</td>
                </tr>
                <tr>
                    <td><code>tooltip</code></td>
                    <td>string</td>
                    <td>Help text</td>
                    <td>"Must be a valid URL"</td>
                </tr>
                <tr>
                    <td><code>min</code></td>
                    <td>int</td>
                    <td>Min value (number)</td>
                    <td>18</td>
                </tr>
                <tr>
                    <td><code>max</code></td>
                    <td>int</td>
                    <td>Max value (number)</td>
                    <td>120</td>
                </tr>
                <tr>
                    <td><code>minlength</code></td>
                    <td>int</td>
                    <td>Min length (text)</td>
                    <td>3</td>
                </tr>
                <tr>
                    <td><code>pattern</code></td>
                    <td>string</td>
                    <td>Regex pattern</td>
                    <td>"[0-9]{3}-[0-9]{4}"</td>
                </tr>
                <tr>
                    <td><code>readonly</code></td>
                    <td>bool</td>
                    <td>Read-only field</td>
                    <td>true</td>
                </tr>
                <tr>
                    <td><code>hidden</code></td>
                    <td>bool</td>
                    <td>Hide from form</td>
                    <td>true</td>
                </tr>
                <tr>
                    <td><code>autocomplete</code></td>
                    <td>string</td>
                    <td>Autocomplete hint</td>
                    <td>"email", "tel", "off"</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
        <h3>📚 Next Steps</h3>
        <ul>
            <li><a href="file-uploads.php">File Uploads</a> - Secure file handling with MIME validation</li>
            <li><a href="../04-advanced/hooks.php">Hooks System</a> - Add custom logic to lifecycle events</li>
            <li><a href="../02-relationships/many-to-many.php">Back to M:N Relationships</a></li>
        </ul>
    </div>
    <script src="../assets/dynamiccrud.js"></script>
</body>
</html>
