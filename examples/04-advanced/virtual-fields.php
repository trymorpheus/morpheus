<?php
/**
 * DynamicCRUD - Virtual Fields
 * 
 * Virtual fields appear in forms but aren't stored in the database.
 * Perfect for: password confirmation, terms acceptance, captcha, etc.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;
use Morpheus\VirtualField;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$crud = new Morpheus($pdo, 'users');

// Virtual Field 1: Password Confirmation
$crud->addVirtualField(new VirtualField(
    name: 'password_confirmation',
    type: 'password',
    label: 'Confirm Password',
    required: true,
    validator: fn($value, $data) => isset($data['password']) && $value === $data['password'],
    attributes: [
        'placeholder' => 'Repeat your password',
        'minlength' => 8,
        'tooltip' => 'Must match the password above',
        'error_message' => 'Passwords do not match'
    ]
));

// Virtual Field 2: Terms Acceptance
$crud->addVirtualField(new VirtualField(
    name: 'terms_accepted',
    type: 'checkbox',
    label: 'I accept the terms and conditions',
    required: true,
    validator: fn($value, $data) => $value === '1',
    attributes: [
        'error_message' => 'You must accept the terms and conditions'
    ]
));

// Hook: Hash password before saving
$crud->beforeSave(function($data) {
    if (isset($data['password']) && !empty($data['password'])) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    return $data;
});

// Hook: Log after user registration
$crud->afterCreate(function($id, $data) {
    error_log("✓ New user registered: ID $id, Email: {$data['email']}");
});

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        header("Location: ?success=User registered successfully! ID: {$result['id']}");
        exit;
    } else {
        $error = $result['error'] ?? 'Validation failed';
        $errors = $result['errors'] ?? [];
    }
}

$stmt = $pdo->query('SELECT id, name, email, created_at FROM users ORDER BY id DESC LIMIT 10');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Fields - DynamicCRUD</title>
    <link rel="stylesheet" href="../assets/dynamiccrud.css">
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .badge { background: #20c997; color: white; padding: 4px 12px; border-radius: 4px; font-size: 12px; }
        .container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .card { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .alert { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info-box { background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        th { background: #f8f9fa; font-weight: 600; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 13px; }
        @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <h1>🔐 Virtual Fields</h1>
    <span class="badge">Not Stored in DB</span>
    <p style="color: #666; margin: 10px 0 20px 0;">
        Fields that appear in forms but aren't saved to the database.
    </p>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-error">
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
        <strong>🎯 Virtual Fields in This Demo:</strong>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
            <li><code>password_confirmation</code> - Validates password match</li>
            <li><code>terms_accepted</code> - Required checkbox for terms</li>
        </ul>
        <p style="margin: 10px 0 0 0;"><strong>Note:</strong> These fields are validated but never saved to the database!</p>
    </div>

    <div class="container">
        <div class="card">
            <h2>User Registration</h2>
            <p style="color: #666; font-size: 14px;">
                Notice the password confirmation and terms acceptance fields!
            </p>
            <?= $crud->renderForm() ?>
        </div>

        <div class="card">
            <h2>Registered Users</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h3>💡 Code Example</h3>
        <pre style="background: white; padding: 15px; border-radius: 4px; overflow-x: auto;"><code>$crud->addVirtualField(new VirtualField(
    name: 'password_confirmation',
    type: 'password',
    label: 'Confirm Password',
    required: true,
    validator: fn($value, $data) => $value === $data['password'],
    attributes: [
        'placeholder' => 'Repeat your password',
        'error_message' => 'Passwords do not match'
    ]
));

// Hash password before saving
$crud->beforeSave(function($data) {
    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    return $data;
});</code></pre>
    </div>

    <div style="margin-top: 20px; padding: 20px; background: #fff3cd; border-radius: 8px;">
        <h3>📋 Common Use Cases</h3>
        <ul>
            <li><strong>Password Confirmation</strong> - Ensure user typed password correctly</li>
            <li><strong>Terms Acceptance</strong> - Legal requirement for registration</li>
            <li><strong>Captcha</strong> - Anti-spam validation</li>
            <li><strong>Calculated Fields</strong> - Fields computed from other inputs</li>
            <li><strong>Temporary Data</strong> - Data needed for validation but not storage</li>
        </ul>
    </div>

    <div style="margin-top: 20px; padding: 20px; background: #e7f3ff; border-radius: 8px;">
        <h3>📚 Next Steps</h3>
        <ul>
            <li><a href="validation.php">Custom Validation</a> - Advanced validation rules</li>
            <li><a href="../05-features/i18n.php">Internationalization</a> - Multi-language support</li>
            <li><a href="hooks.php">Back to Hooks</a></li>
        </ul>
    </div>
    <script src="../assets/dynamiccrud.js"></script>
</body>
</html>
