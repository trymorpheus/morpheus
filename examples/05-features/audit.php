<?php
/**
 * DynamicCRUD - Audit Logging
 * 
 * Track all database changes with automatic audit logging.
 * Logs: action, user_id, IP, old_values, new_values, timestamp
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Simulate logged-in user
$currentUserId = 1;

$crud = new Morpheus($pdo, 'users');

// Enable audit logging
$crud->enableAudit($currentUserId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $crud->handleSubmission();
        
        if ($result['success']) {
            $action = isset($_POST['id']) ? 'updated' : 'created';
            header("Location: ?success=User $action with ID: {$result['id']}");
            exit;
        } else {
            $error = $result['error'] ?? 'Validation failed';
            $errors = $result['errors'] ?? [];
        }
    } catch (\Exception $e) {
        $error = 'Exception: ' . $e->getMessage();
        error_log($error);
    }
}

// Get users
$stmt = $pdo->query('SELECT id, name, email FROM users ORDER BY id DESC LIMIT 10');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get audit logs
$stmt = $pdo->query('SELECT * FROM audit_log WHERE table_name = "users" ORDER BY created_at DESC LIMIT 20');
$auditLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logging - DynamicCRUD</title>
    <link rel="stylesheet" href="../assets/dynamiccrud.css">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 1400px; margin: 40px auto; padding: 0 20px; }
        .badge { background: #6c757d; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .info-box { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #f8f9fa; font-weight: 600; }
        a { color: #007bff; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .action-create { color: #28a745; font-weight: bold; }
        .action-update { color: #ffc107; font-weight: bold; }
        .action-delete { color: #dc3545; font-weight: bold; }
        .json-preview { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-family: monospace; font-size: 11px; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 13px; }
        @media (max-width: 1024px) { .grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <h1>📊 Audit Logging</h1>
    <span class="badge">Change Tracking</span>
    <p style="color: #666; margin: 10px 0 20px 0;">
        Automatic logging of all database changes with user tracking and IP addresses.
    </p>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            ✗ Error: <?= htmlspecialchars($error) ?>
            <?php if (!empty($errors)): ?>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <?php foreach ($errors as $field => $fieldErrors): ?>
                        <?php foreach ($fieldErrors as $err): ?>
                            <li><?= htmlspecialchars($err) ?></li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="info-box">
        <strong>🎯 What's Logged:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            <li><strong>Action:</strong> create, update, delete</li>
            <li><strong>User ID:</strong> Who made the change</li>
            <li><strong>IP Address:</strong> Where the change came from</li>
            <li><strong>Old Values:</strong> Data before change (JSON)</li>
            <li><strong>New Values:</strong> Data after change (JSON)</li>
            <li><strong>Timestamp:</strong> When the change occurred</li>
        </ul>
    </div>

    <div class="grid">
        <div class="card">
            <h2><?= $id ? 'Edit User' : 'Create User' ?></h2>
            <p style="color: #666; font-size: 14px;">
                All changes are automatically logged!
            </p>
            <?= $crud->renderForm($id) ?>
            
            <?php if ($id): ?>
                <p style="margin-top: 15px;"><a href="audit.php" style="color: #007bff; text-decoration: none;">← Create new user</a></p>
            <?php endif; ?>
            
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
                <strong>💡 Try it:</strong>
                <ol style="margin: 10px 0 0 0; padding-left: 20px; font-size: 14px;">
                    <li>Create a new user</li>
                    <li>Edit the user (click Edit below)</li>
                    <li>Check the audit log →</li>
                </ol>
            </div>
            
            <h3 style="margin-top: 30px;">Users</h3>
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
                        <td><a href="?id=<?= $user['id'] ?>" style="color: #007bff; text-decoration: none;">Edit</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card" style="overflow-x: auto;">
            <h2>Audit Log (Last 20 Changes)</h2>
            <?php if (!empty($auditLogs)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Action</th>
                            <th>Record</th>
                            <th>User</th>
                            <th>IP</th>
                            <th>Changes</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($auditLogs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['id']) ?></td>
                            <td class="action-<?= htmlspecialchars($log['action']) ?>">
                                <?= strtoupper(htmlspecialchars($log['action'])) ?>
                            </td>
                            <td><?= htmlspecialchars($log['record_id']) ?></td>
                            <td><?= htmlspecialchars($log['user_id'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($log['ip_address'] ?? 'N/A') ?></td>
                            <td>
                                <?php if ($log['action'] === 'create'): ?>
                                    <span class="json-preview" title="<?= htmlspecialchars($log['new_values']) ?>">
                                        <?= htmlspecialchars(substr($log['new_values'], 0, 50)) ?>...
                                    </span>
                                <?php elseif ($log['action'] === 'update'): ?>
                                    <span class="json-preview" title="Old: <?= htmlspecialchars($log['old_values']) ?> | New: <?= htmlspecialchars($log['new_values']) ?>">
                                        Changed fields
                                    </span>
                                <?php else: ?>
                                    <span class="json-preview" title="<?= htmlspecialchars($log['old_values']) ?>">
                                        Deleted
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($log['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #666;">No audit logs yet. Make some changes to see them here!</p>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>💡 Usage</h3>
        <pre style="background: white; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>$crud = new Morpheus($pdo, 'users');

// Enable audit logging with user ID
$crud->enableAudit($currentUserId);

// All operations are now logged automatically
$crud->handleSubmission(); // Logged!
$crud->delete($id);        // Logged!</code></pre>
    </div>

    <div style="margin-top: 20px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
        <h3>📚 Next Steps</h3>
        <ul>
            <li><a href="i18n.php">Internationalization</a> - Multi-language support</li>
            <li><a href="../04-advanced/hooks.php">Back to Hooks</a></li>
        </ul>
    </div>
    <script src="../assets/dynamiccrud.js"></script>
</body>
</html>
