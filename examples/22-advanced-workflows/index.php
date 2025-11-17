<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\DynamicCRUD;
use Morpheus\Workflow\WorkflowTemplate;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');

// Create table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS support_tickets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status VARCHAR(50) DEFAULT 'open',
    assigned_to INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$crud = new Morpheus($pdo, 'support_tickets');

// Use template + add conditional transitions and escalations
$workflowConfig = WorkflowTemplate::ticketSupport();

// Add conditional transition (only high/urgent tickets can be escalated)
$workflowConfig['transitions']['escalate'] = [
    'from' => ['open', 'in_progress'],
    'to' => 'in_progress',
    'label' => 'Escalate to Manager',
    'permissions' => ['support', 'admin'],
    'conditions' => ['priority' => ['high', 'urgent']], // Only for high/urgent
    'color' => '#ef4444'
];

// Add escalation rules (auto-escalate if stuck too long)
$workflowConfig['escalations'] = [
    [
        'state' => 'open',
        'timeout' => 3600, // 1 hour
        'action' => 'notify_manager',
        'message' => 'Ticket has been open for over 1 hour'
    ],
    [
        'state' => 'waiting_customer',
        'timeout' => 86400, // 24 hours
        'action' => 'auto_close',
        'message' => 'No customer response for 24 hours'
    ]
];

$crud->enableWorkflow($workflowConfig);

// Handle workflow transitions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['workflow_transition'])) {
    $user = ['id' => 1, 'role' => 'support']; // Simulated user
    $result = $crud->transition((int)$_POST['workflow_id'], $_POST['workflow_transition'], $user);
    
    if ($result['success']) {
        $message = "✅ Transition successful: {$result['from']} → {$result['to']}";
    } else {
        $message = "❌ Error: {$result['error']}";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['workflow_transition'])) {
    $result = $crud->handleSubmission();
    if ($result['success']) {
        header('Location: ?id=' . $result['id']);
        exit;
    }
}

// Get analytics
$workflow = $crud->getWorkflowEngine();
$analytics = $workflow ? $workflow->getAnalytics() : null;

// Check escalations
$escalations = $workflow ? $workflow->checkEscalations() : [];

$id = $_GET['id'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Advanced Workflows - Support Tickets</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .message { padding: 15px; margin: 20px 0; border-radius: 6px; }
        .message.success { background: #d1fae5; color: #065f46; }
        .message.error { background: #fee2e2; color: #991b1b; }
        .analytics { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 30px 0; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea; }
        .stat-card h3 { margin: 0 0 10px 0; font-size: 14px; color: #666; }
        .stat-card .value { font-size: 32px; font-weight: bold; color: #333; }
        .escalations { background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0; border-radius: 4px; }
        .escalations h3 { margin: 0 0 15px 0; color: #856404; }
        .escalation-item { background: white; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .templates { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin: 20px 0; }
        .template-card { background: #f8f9fa; padding: 15px; border-radius: 6px; border: 2px solid #e2e8f0; }
        .template-card h4 { margin: 0 0 10px 0; color: #667eea; }
        .template-card ul { margin: 10px 0; padding-left: 20px; font-size: 14px; }
    </style>
</head>
<body>
    <h1>🎫 Advanced Workflows - Support Tickets</h1>
    
    <?php if (isset($message)): ?>
        <div class="message <?= $result['success'] ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($analytics): ?>
        <h2>📊 Workflow Analytics</h2>
        <div class="analytics">
            <div class="stat-card">
                <h3>Total Tickets</h3>
                <div class="value"><?= $analytics['total'] ?></div>
            </div>
            <?php foreach ($analytics['by_state'] as $state => $count): ?>
                <div class="stat-card">
                    <h3><?= ucfirst($state) ?></h3>
                    <div class="value"><?= $count ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (!empty($analytics['transitions'])): ?>
            <h3>Transition Stats</h3>
            <ul>
                <?php foreach ($analytics['transitions'] as $transition => $count): ?>
                    <li><strong><?= htmlspecialchars($transition) ?>:</strong> <?= $count ?> times</li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (!empty($escalations)): ?>
        <div class="escalations">
            <h3>⚠️ Escalations Required (<?= count($escalations) ?>)</h3>
            <?php foreach ($escalations as $esc): ?>
                <div class="escalation-item">
                    <strong>Ticket #<?= $esc['record_id'] ?></strong> - 
                    State: <?= $esc['state'] ?> - 
                    Action: <?= $esc['action'] ?> - 
                    <?= $esc['rule']['message'] ?? 'Needs attention' ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <h2>Available Workflow Templates</h2>
    <div class="templates">
        <div class="template-card">
            <h4>🛒 Order Management</h4>
            <ul>
                <li>States: pending, processing, shipped, delivered, cancelled</li>
                <li>Multi-stage approval</li>
                <li>Role-based transitions</li>
            </ul>
        </div>
        <div class="template-card">
            <h4>🎫 Ticket Support (Active)</h4>
            <ul>
                <li>States: open, in_progress, waiting_customer, resolved, closed</li>
                <li>Conditional transitions</li>
                <li>Auto-escalation rules</li>
            </ul>
        </div>
        <div class="template-card">
            <h4>✅ Approval Process</h4>
            <ul>
                <li>States: draft, pending_review, approved, rejected</li>
                <li>Manager approval required</li>
                <li>Revision workflow</li>
            </ul>
        </div>
        <div class="template-card">
            <h4>📝 Content Publishing</h4>
            <ul>
                <li>States: draft, review, scheduled, published, archived</li>
                <li>Editorial workflow</li>
                <li>Scheduling support</li>
            </ul>
        </div>
    </div>
    
    <h2><?= $id ? 'Edit' : 'Create' ?> Ticket</h2>
    <?= $crud->renderForm($id) ?>
    
    <h3>Features Demonstrated</h3>
    <ul>
        <li>✅ <strong>Workflow Templates</strong> - Pre-built workflows for common scenarios</li>
        <li>✅ <strong>Conditional Transitions</strong> - Escalate only high/urgent tickets</li>
        <li>✅ <strong>Escalation Rules</strong> - Auto-escalate stuck tickets</li>
        <li>✅ <strong>Workflow Analytics</strong> - Real-time stats by state</li>
        <li>✅ <strong>Transition History</strong> - Complete audit trail</li>
        <li>✅ <strong>Multi-stage Approval</strong> - Complex workflows made easy</li>
    </ul>
    
    <p><a href="?">← Back to list</a> | <a href="../">← All Examples</a></p>
</body>
</html>
