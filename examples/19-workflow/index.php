<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;

$pdo = new PDO('mysql:host=localhost;dbname=test;charset=utf8mb4', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Simulate user session
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'admin'; // admin, manager, warehouse, guest
}

$crud = new Morpheus($pdo, 'orders');

// Set current user for permission checks
$crud->setCurrentUser($_SESSION['user_id'], $_SESSION['role']);

// Enable workflow
$crud->enableWorkflow([
    'field' => 'status',
    'states' => ['pending', 'processing', 'shipped', 'delivered', 'cancelled'],
    'transitions' => [
        'process' => [
            'from' => 'pending',
            'to' => 'processing',
            'label' => 'Procesar Pedido',
            'color' => '#3b82f6',
            'permissions' => ['admin', 'manager']
        ],
        'ship' => [
            'from' => 'processing',
            'to' => 'shipped',
            'label' => 'Enviar',
            'color' => '#8b5cf6',
            'permissions' => ['admin', 'warehouse']
        ],
        'deliver' => [
            'from' => 'shipped',
            'to' => 'delivered',
            'label' => 'Entregar',
            'color' => '#10b981',
            'permissions' => ['admin', 'warehouse']
        ],
        'cancel' => [
            'from' => ['pending', 'processing'],
            'to' => 'cancelled',
            'label' => 'Cancelar',
            'color' => '#ef4444',
            'permissions' => ['admin']
        ]
    ],
    'state_labels' => [
        'pending' => ['label' => 'Pendiente', 'color' => '#f59e0b'],
        'processing' => ['label' => 'Procesando', 'color' => '#3b82f6'],
        'shipped' => ['label' => 'Enviado', 'color' => '#8b5cf6'],
        'delivered' => ['label' => 'Entregado', 'color' => '#10b981'],
        'cancelled' => ['label' => 'Cancelado', 'color' => '#ef4444']
    ],
    'history' => true,
    'history_table' => '_workflow_history'
]);

// Add workflow hooks
$crud->getWorkflowEngine()->addHook('before_process', function($id, $from, $to, $user) {
    error_log("Order #$id is being processed by user {$user['id']}");
});

$crud->getWorkflowEngine()->addHook('after_deliver', function($id, $from, $to, $user) {
    error_log("Order #$id has been delivered!");
    // Here you could send email notification, update inventory, etc.
});

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        $message = isset($result['from']) && isset($result['to'])
            ? "✅ Transición exitosa: {$result['from']} → {$result['to']}"
            : "✅ Pedido guardado correctamente (ID: {$result['id']})";
        header("Location: ?id={$result['id']}&success=" . urlencode($message));
        exit;
    } else {
        $error = $result['error'] ?? 'Error desconocido';
    }
}

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? 'form';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workflow Engine - Order Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; background: #f7fafc; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .header h1 { color: #2d3748; margin-bottom: 10px; }
        .header p { color: #718096; }
        .nav { display: flex; gap: 10px; margin-bottom: 20px; }
        .nav a { padding: 10px 20px; background: white; color: #667eea; text-decoration: none; border-radius: 6px; font-weight: 500; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .nav a:hover { background: #667eea; color: white; }
        .nav a.active { background: #667eea; color: white; }
        .content { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .alert-error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .user-info { background: #e8f0fe; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .user-info strong { color: #1e40af; }
        .role-selector { margin-top: 10px; }
        .role-selector select { padding: 8px; border-radius: 4px; border: 1px solid #cbd5e0; }
        .history-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .history-table th, .history-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .history-table th { background: #f7fafc; font-weight: 600; color: #2d3748; }
        .history-table tr:hover { background: #f7fafc; }
        .state-badge { padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 500; color: white; }
        .list-table { width: 100%; border-collapse: collapse; }
        .list-table th, .list-table td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .list-table th { background: #f7fafc; font-weight: 600; color: #2d3748; }
        .list-table tr:hover { background: #f7fafc; }
        .action-edit, .action-delete { display: inline-block; padding: 6px 12px; margin-right: 5px; border-radius: 4px; text-decoration: none; font-size: 13px; }
        .action-edit { background: #667eea; color: white; }
        .action-edit:hover { opacity: 0.8; }
        .action-delete { background: #e53e3e; color: white; }
        .action-delete:hover { opacity: 0.8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛒 Workflow Engine - Order Management</h1>
            <p>Sistema de gestión de pedidos con estados y transiciones configurables</p>
        </div>

        <div class="content" style="background: #e8f0fe; border-left: 4px solid #1e40af; margin: 20px; padding: 20px; border-radius: 6px;">
            <h3 style="color: #1e40af; margin-bottom: 15px;">📖 Cómo probar este ejemplo:</h3>
            <ol style="color: #1e3a8a; line-height: 1.8;">
                <li><strong>Crear un pedido:</strong> Haz clic en "📝 Nuevo Pedido" y completa el formulario. El pedido se creará en estado "pending".</li>
                <li><strong>Ver transiciones disponibles:</strong> Edita un pedido desde la lista. Verás botones de transición según el estado actual y tu rol.</li>
                <li><strong>Cambiar de rol:</strong> Usa el selector de rol arriba para probar diferentes permisos:
                    <ul style="margin-top: 8px;">
                        <li><strong>Admin:</strong> Puede ejecutar todas las transiciones</li>
                        <li><strong>Manager:</strong> Puede procesar pedidos (pending → processing)</li>
                        <li><strong>Warehouse:</strong> Puede enviar y entregar (processing → shipped → delivered)</li>
                        <li><strong>Guest:</strong> No puede ejecutar ninguna transición</li>
                    </ul>
                </li>
                <li><strong>Ejecutar transiciones:</strong> Haz clic en los botones de transición para cambiar el estado del pedido.</li>
                <li><strong>Ver historial:</strong> Haz clic en "📜 Historial" para ver todas las transiciones realizadas con usuario, IP y timestamp.</li>
                <li><strong>Observar colores:</strong> Cada estado tiene un color diferente en la lista y en los badges.</li>
            </ol>
            <p style="margin-top: 15px; color: #1e3a8a;"><strong>💡 Tip:</strong> Intenta ejecutar una transición sin permisos para ver cómo el sistema lo previene automáticamente.</p>
        </div>

        <div class="user-info">
            <strong>Usuario actual:</strong> User #<?= $_SESSION['user_id'] ?> 
            <strong>Rol:</strong> <span style="background: #667eea; color: white; padding: 2px 8px; border-radius: 4px; font-weight: 600;"><?= htmlspecialchars($_SESSION['role']) ?></span>
            <div class="role-selector">
                <form method="POST" action="change-role.php" style="display: inline;">
                    <label><strong>Cambiar rol para probar permisos:</strong> </label>
                    <select name="role" onchange="this.form.submit()" style="padding: 6px; border-radius: 4px; border: 2px solid #667eea; font-weight: 500;">
                        <option value="admin" <?= $_SESSION['role'] === 'admin' ? 'selected' : '' ?>>👑 Admin (todos los permisos)</option>
                        <option value="manager" <?= $_SESSION['role'] === 'manager' ? 'selected' : '' ?>>👔 Manager (puede procesar)</option>
                        <option value="warehouse" <?= $_SESSION['role'] === 'warehouse' ? 'selected' : '' ?>>📦 Warehouse (puede enviar/entregar)</option>
                        <option value="guest" <?= $_SESSION['role'] === 'guest' ? 'selected' : '' ?>>👤 Guest (sin permisos)</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="nav">
            <a href="?" class="<?= !$action || $action === 'form' ? 'active' : '' ?>">📝 Nuevo Pedido</a>
            <a href="?action=list" class="<?= $action === 'list' ? 'active' : '' ?>">📋 Lista de Pedidos</a>
            <?php if ($id): ?>
            <a href="?action=history&id=<?= $id ?>" class="<?= $action === 'history' ? 'active' : '' ?>">📜 Historial</a>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <div class="content">
            <?php if ($action === 'list'): ?>
                <h2 style="margin-bottom: 20px;">Lista de Pedidos</h2>
                <?php
                // Handle delete
                if (isset($_GET['delete'])) {
                    $crud->delete((int)$_GET['delete']);
                    header("Location: ?action=list&deleted=1");
                    exit;
                }
                
                $list = $crud->renderList();
                
                // Fix edit/delete links
                $list = preg_replace('/href="\?[^"]*id=(\d+)"/', 'href="?id=$1"', $list);
                $list = preg_replace('/href="\?[^"]*delete=(\d+)"/', 'href="?action=list&delete=$1"', $list);
                
                // Enhance state column with badges
                $workflowEngine = $crud->getWorkflowEngine();
                foreach (['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $state) {
                    $badge = $workflowEngine->renderStateColumn($state);
                    $list = str_replace(">$state<", ">$badge<", $list);
                }
                
                // Add success message for delete
                if (isset($_GET['deleted'])) {
                    echo '<div class="alert alert-success">✅ Pedido eliminado correctamente</div>';
                }
                
                echo $list;
                ?>
            <?php elseif ($action === 'history' && $id): ?>
                <h2 style="margin-bottom: 20px;">Historial de Transiciones - Pedido #<?= $id ?></h2>
                <?php
                $history = $crud->getWorkflowHistory((int)$id);
                if (empty($history)):
                ?>
                <p>No hay historial de transiciones para este pedido.</p>
                <?php else: ?>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Transición</th>
                            <th>Estado Anterior</th>
                            <th>Estado Nuevo</th>
                            <th>Usuario</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $entry): ?>
                        <tr>
                            <td><?= htmlspecialchars($entry['created_at']) ?></td>
                            <td><strong><?= htmlspecialchars($entry['transition']) ?></strong></td>
                            <td><?= $entry['from_state'] ? $crud->getWorkflowEngine()->renderStateColumn($entry['from_state']) : '-' ?></td>
                            <td><?= $crud->getWorkflowEngine()->renderStateColumn($entry['to_state']) ?></td>
                            <td>User #<?= htmlspecialchars($entry['user_id'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($entry['user_ip'] ?? 'N/A') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
                <div style="margin-top: 20px;">
                    <a href="?id=<?= $id ?>" style="color: #667eea; text-decoration: none;">← Volver al pedido</a>
                </div>
            <?php else: ?>
                <h2 style="margin-bottom: 20px;"><?= $id ? 'Editar Pedido #' . $id : 'Nuevo Pedido' ?></h2>
                <?= $crud->renderForm($id) ?>
                <?php if ($id): ?>
                <div style="margin-top: 20px;">
                    <a href="?action=history&id=<?= $id ?>" style="color: #667eea; text-decoration: none;">📜 Ver historial de transiciones</a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
