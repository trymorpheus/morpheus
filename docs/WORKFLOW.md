# Workflow Engine Guide

The Workflow Engine allows you to define states and transitions for your records with permission control and complete history tracking.

## Table of Contents

- [Overview](#overview)
- [Basic Usage](#basic-usage)
- [Configuration](#configuration)
- [Transitions](#transitions)
- [Permissions](#permissions)
- [History Tracking](#history-tracking)
- [Lifecycle Hooks](#lifecycle-hooks)
- [UI Components](#ui-components)
- [Use Cases](#use-cases)
- [Best Practices](#best-practices)

## Overview

The Workflow Engine provides:

- ✅ **State Management** - Define allowed states for records
- ✅ **Transitions** - Configure transitions between states
- ✅ **Permission Control** - Restrict transitions by user role
- ✅ **Automatic UI** - Transition buttons rendered automatically
- ✅ **History Tracking** - Complete audit trail of all transitions
- ✅ **Lifecycle Hooks** - Execute custom logic before/after transitions
- ✅ **State Labels** - Custom labels and colors for each state

## Basic Usage

### 1. Enable Workflow

```php
use Morpheus\DynamicCRUD;

$crud = new Morpheus($pdo, 'orders');

$crud->enableWorkflow([
    'field' => 'status',
    'states' => ['pending', 'processing', 'shipped', 'delivered'],
    'transitions' => [
        'process' => [
            'from' => 'pending',
            'to' => 'processing',
            'label' => 'Process Order'
        ],
        'ship' => [
            'from' => 'processing',
            'to' => 'shipped',
            'label' => 'Ship Order'
        ]
    ]
]);
```

### 2. Render Form with Transitions

```php
// Transition buttons appear automatically when editing
echo $crud->renderForm($orderId);
```

### 3. Handle Transitions

```php
// Transitions are handled automatically via form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $crud->handleSubmission();
    
    if ($result['success']) {
        if (isset($result['from']) && isset($result['to'])) {
            echo "Transitioned from {$result['from']} to {$result['to']}";
        }
    }
}
```

## Configuration

### Workflow Config Structure

```php
[
    'field' => 'status',                    // Column name for state
    'states' => [...],                      // Array of allowed states
    'transitions' => [...],                 // Transition definitions
    'state_labels' => [...],                // Custom labels and colors (optional)
    'history' => true,                      // Enable history tracking (optional)
    'history_table' => '_workflow_history'  // History table name (optional)
]
```

### States

Define all possible states for your records:

```php
'states' => ['pending', 'processing', 'shipped', 'delivered', 'cancelled']
```

### State Labels

Customize how states are displayed:

```php
'state_labels' => [
    'pending' => [
        'label' => 'Pendiente',
        'color' => '#f59e0b'
    ],
    'processing' => [
        'label' => 'Procesando',
        'color' => '#3b82f6'
    ],
    'shipped' => [
        'label' => 'Enviado',
        'color' => '#8b5cf6'
    ],
    'delivered' => [
        'label' => 'Entregado',
        'color' => '#10b981'
    ]
]
```

## Transitions

### Basic Transition

```php
'process' => [
    'from' => 'pending',
    'to' => 'processing',
    'label' => 'Process Order',
    'color' => '#3b82f6'
]
```

### Multiple From States

A transition can start from multiple states:

```php
'cancel' => [
    'from' => ['pending', 'processing'],  // Can cancel from either state
    'to' => 'cancelled',
    'label' => 'Cancel Order',
    'color' => '#ef4444'
]
```

### Transition Options

| Option | Type | Description | Required |
|--------|------|-------------|----------|
| `from` | string\|array | Source state(s) | Yes |
| `to` | string | Target state | Yes |
| `label` | string | Button label | No (defaults to ucfirst(name)) |
| `color` | string | Button color (hex) | No (defaults to #667eea) |
| `permissions` | array | Required roles | No (allows all if not set) |

## Permissions

### Role-Based Transitions

Restrict transitions to specific roles:

```php
'transitions' => [
    'process' => [
        'from' => 'pending',
        'to' => 'processing',
        'permissions' => ['admin', 'manager']  // Only admin and manager can process
    ],
    'ship' => [
        'from' => 'processing',
        'to' => 'shipped',
        'permissions' => ['admin', 'warehouse']  // Only admin and warehouse can ship
    ]
]
```

### Checking Permissions

```php
$workflowEngine = $crud->getWorkflowEngine();

$user = ['id' => 1, 'role' => 'manager'];
$canProcess = $workflowEngine->canTransition('process', 'pending', $user);

if ($canProcess) {
    echo "User can process orders";
}
```

### Getting Available Transitions

```php
$currentState = $workflowEngine->getCurrentState($orderId);
$available = $workflowEngine->getAvailableTransitions($currentState, $user);

foreach ($available as $name => $config) {
    echo "Can execute: {$config['label']} → {$config['to']}";
}
```

## History Tracking

### Enable History

```php
$crud->enableWorkflow([
    'field' => 'status',
    'states' => [...],
    'transitions' => [...],
    'history' => true,
    'history_table' => '_workflow_history'  // Optional, defaults to _workflow_history
]);
```

### History Table Structure

The history table is created automatically with:

```sql
CREATE TABLE _workflow_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(255) NOT NULL,
    record_id INT NOT NULL,
    transition VARCHAR(100) NOT NULL,
    from_state VARCHAR(100),
    to_state VARCHAR(100) NOT NULL,
    user_id INT,
    user_ip VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_record (table_name, record_id),
    INDEX idx_created (created_at)
);
```

### Retrieving History

```php
$history = $crud->getWorkflowHistory($orderId);

foreach ($history as $entry) {
    echo "{$entry['transition']}: {$entry['from_state']} → {$entry['to_state']}";
    echo " by User #{$entry['user_id']} at {$entry['created_at']}";
}
```

## Lifecycle Hooks

### Available Hooks

Hooks are named: `before_{transition}` and `after_{transition}`

```php
$workflowEngine = $crud->getWorkflowEngine();

// Before transition
$workflowEngine->addHook('before_process', function($id, $from, $to, $user) {
    // Validate before processing
    $order = getOrder($id);
    if ($order['payment_status'] !== 'paid') {
        throw new Exception('Cannot process unpaid order');
    }
});

// After transition
$workflowEngine->addHook('after_ship', function($id, $from, $to, $user) {
    // Send shipping notification
    $order = getOrder($id);
    sendEmail($order['customer_email'], 'Order Shipped', '...');
});
```

### Hook Parameters

All hooks receive:
- `$id` - Record ID
- `$from` - Previous state
- `$to` - New state
- `$user` - User array with `id` and `role` (if provided)

### Preventing Transitions

Throw an exception in a `before_` hook to prevent the transition:

```php
$workflowEngine->addHook('before_deliver', function($id, $from, $to, $user) {
    if (!isAddressValid($id)) {
        throw new Exception('Cannot deliver: Invalid delivery address');
    }
});
```

## UI Components

### Transition Buttons

Buttons are automatically rendered after the form when editing a record:

```html
┌─────────────────────────────────────┐
│ Estado actual: [Pending]            │
│ [Process Order] [Cancel]            │
└─────────────────────────────────────┘
```

Features:
- Only shows available transitions for current state
- Respects user permissions
- Custom colors per transition
- Automatic form submission

### State Badges

Render colored state badges in lists:

```php
$workflowEngine = $crud->getWorkflowEngine();
$badge = $workflowEngine->renderStateColumn('pending');
echo $badge; // <span class="workflow-state-badge" style="...">Pendiente</span>
```

## Use Cases

### 1. Order Management

```php
'states' => ['pending', 'processing', 'shipped', 'delivered', 'cancelled'],
'transitions' => [
    'process' => ['from' => 'pending', 'to' => 'processing'],
    'ship' => ['from' => 'processing', 'to' => 'shipped'],
    'deliver' => ['from' => 'shipped', 'to' => 'delivered'],
    'cancel' => ['from' => ['pending', 'processing'], 'to' => 'cancelled']
]
```

### 2. Ticket System

```php
'states' => ['open', 'assigned', 'in_progress', 'resolved', 'closed'],
'transitions' => [
    'assign' => ['from' => 'open', 'to' => 'assigned'],
    'start' => ['from' => 'assigned', 'to' => 'in_progress'],
    'resolve' => ['from' => 'in_progress', 'to' => 'resolved'],
    'close' => ['from' => 'resolved', 'to' => 'closed'],
    'reopen' => ['from' => 'closed', 'to' => 'open']
]
```

### 3. Content Publishing

```php
'states' => ['draft', 'review', 'approved', 'published', 'archived'],
'transitions' => [
    'submit' => ['from' => 'draft', 'to' => 'review', 'permissions' => ['author']],
    'approve' => ['from' => 'review', 'to' => 'approved', 'permissions' => ['editor']],
    'publish' => ['from' => 'approved', 'to' => 'published', 'permissions' => ['admin']],
    'archive' => ['from' => 'published', 'to' => 'archived', 'permissions' => ['admin']]
]
```

### 4. Approval Workflow

```php
'states' => ['submitted', 'pending_approval', 'approved', 'rejected'],
'transitions' => [
    'submit' => ['from' => 'submitted', 'to' => 'pending_approval'],
    'approve' => ['from' => 'pending_approval', 'to' => 'approved', 'permissions' => ['manager']],
    'reject' => ['from' => 'pending_approval', 'to' => 'rejected', 'permissions' => ['manager']],
    'resubmit' => ['from' => 'rejected', 'to' => 'submitted']
]
```

## Best Practices

### 1. Use Descriptive State Names

```php
// Good
'states' => ['pending', 'processing', 'shipped', 'delivered']

// Avoid
'states' => ['s1', 's2', 's3', 's4']
```

### 2. Provide Clear Labels

```php
'transitions' => [
    'process' => [
        'from' => 'pending',
        'to' => 'processing',
        'label' => 'Process Order'  // Clear action
    ]
]
```

### 3. Use Hooks for Side Effects

```php
// Good - Side effects in hooks
$workflowEngine->addHook('after_deliver', function($id) {
    sendDeliveryEmail($id);
    updateInventory($id);
});

// Avoid - Side effects in application code
$crud->transition($id, 'deliver');
sendDeliveryEmail($id);  // Can be missed
```

### 4. Enable History for Audit

```php
// Always enable history for important workflows
$crud->enableWorkflow([
    'field' => 'status',
    'states' => [...],
    'transitions' => [...],
    'history' => true  // Track all changes
]);
```

### 5. Validate in Before Hooks

```php
// Validate business rules before transition
$workflowEngine->addHook('before_ship', function($id) {
    $order = getOrder($id);
    
    if (!$order['payment_confirmed']) {
        throw new Exception('Payment not confirmed');
    }
    
    if (!hasInventory($order['product_id'])) {
        throw new Exception('Product out of stock');
    }
});
```

### 6. Use Permissions Wisely

```php
// Restrict sensitive transitions
'transitions' => [
    'approve' => [
        'from' => 'pending',
        'to' => 'approved',
        'permissions' => ['manager', 'admin']  // Only managers and admins
    ]
]
```

### 7. Provide Rollback Transitions

```php
// Allow reverting mistakes
'transitions' => [
    'ship' => ['from' => 'processing', 'to' => 'shipped'],
    'unship' => ['from' => 'shipped', 'to' => 'processing']  // Rollback
]
```

## Advanced Examples

### Conditional Transitions

```php
$workflowEngine->addHook('before_ship', function($id, $from, $to, $user) {
    $order = getOrder($id);
    
    // Only allow shipping on weekdays
    $dayOfWeek = date('N');
    if ($dayOfWeek >= 6) {
        throw new Exception('Cannot ship on weekends');
    }
    
    // Only allow shipping if address is verified
    if (!$order['address_verified']) {
        throw new Exception('Address not verified');
    }
});
```

### Notifications on Transition

```php
$workflowEngine->addHook('after_deliver', function($id, $from, $to, $user) {
    $order = getOrder($id);
    
    // Email customer
    sendEmail($order['customer_email'], 'Order Delivered', '
        Your order #' . $id . ' has been delivered!
        Thank you for your purchase.
    ');
    
    // SMS notification
    sendSMS($order['customer_phone'], 'Your order has been delivered!');
    
    // Webhook
    triggerWebhook('https://api.example.com/order-delivered', [
        'order_id' => $id,
        'customer_id' => $order['customer_id'],
        'delivered_at' => date('Y-m-d H:i:s')
    ]);
});
```

### Integration with External Systems

```php
$workflowEngine->addHook('after_process', function($id) {
    $order = getOrder($id);
    
    // Update inventory system
    $inventoryAPI = new InventoryAPI();
    $inventoryAPI->reserveStock($order['product_id'], $order['quantity']);
    
    // Create shipping label
    $shippingAPI = new ShippingAPI();
    $label = $shippingAPI->createLabel($order);
    
    // Update order with tracking number
    updateOrder($id, ['tracking_number' => $label->tracking_number]);
});
```

## Troubleshooting

### Transition Not Allowed

**Problem**: Transition button doesn't appear or returns error

**Solutions**:
1. Check current state matches `from` state
2. Verify user has required permissions
3. Check transition is defined in config

### History Not Saving

**Problem**: History table is empty

**Solutions**:
1. Ensure `history: true` in config
2. Check database permissions
3. Verify history table was created

### Hook Not Executing

**Problem**: Hook code doesn't run

**Solutions**:
1. Check hook name matches transition name
2. Verify hook was added before transition
3. Check for exceptions in hook code

## See Also

- [RBAC Guide](RBAC.md) - Permission system integration
- [Hooks Guide](HOOKS.md) - General hooks system
- [Table Metadata Guide](TABLE_METADATA.md) - Table configuration
- [Audit Logging](../examples/05-features/audit.php) - Change tracking

## Example

See the complete working example at [examples/19-workflow/](../examples/19-workflow/)
