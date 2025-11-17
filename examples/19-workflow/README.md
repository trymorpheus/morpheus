# Workflow Engine Example

This example demonstrates the **Workflow Engine** feature that allows you to define states and transitions for your records with permission control and history tracking.

## Features Demonstrated

- ✅ **State Management** - Define allowed states for records
- ✅ **Transitions** - Configure transitions between states
- ✅ **Permission Control** - Restrict transitions by user role
- ✅ **Transition Buttons** - Automatic UI buttons for available transitions
- ✅ **History Tracking** - Complete audit trail of all transitions
- ✅ **Lifecycle Hooks** - Execute custom logic before/after transitions
- ✅ **State Labels** - Custom labels and colors for each state
- ✅ **Multiple From States** - Transitions can start from multiple states

## Setup

1. Create the database table:
```bash
php -r "$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword'); $sql = file_get_contents('setup.sql'); $pdo->exec($sql);"
```

2. Open in browser:
```
http://localhost:8000/examples/19-workflow/
```

## Workflow Configuration

### States
- **pending** - Order received, awaiting processing
- **processing** - Order is being prepared
- **shipped** - Order has been shipped
- **delivered** - Order delivered to customer
- **cancelled** - Order cancelled

### Transitions

| Transition | From | To | Permissions |
|------------|------|-----|-------------|
| **process** | pending | processing | admin, manager |
| **ship** | processing | shipped | admin, warehouse |
| **deliver** | shipped | delivered | admin, warehouse |
| **cancel** | pending, processing | cancelled | admin |

## Usage

### Basic Workflow Setup

```php
$crud = new Morpheus($pdo, 'orders');

$crud->enableWorkflow([
    'field' => 'status',
    'states' => ['pending', 'processing', 'shipped', 'delivered'],
    'transitions' => [
        'process' => [
            'from' => 'pending',
            'to' => 'processing',
            'label' => 'Process Order',
            'color' => '#3b82f6',
            'permissions' => ['admin', 'manager']
        ]
    ],
    'history' => true
]);
```

### Execute Transition

```php
// Automatic via form submission
$result = $crud->handleSubmission();

// Manual transition
$result = $crud->transition($orderId, 'process', [
    'id' => $userId,
    'role' => 'admin'
]);

if ($result['success']) {
    echo "Transitioned from {$result['from']} to {$result['to']}";
}
```

### Add Hooks

```php
$crud->getWorkflowEngine()->addHook('before_ship', function($id, $from, $to, $user) {
    // Validate inventory before shipping
    if (!checkInventory($id)) {
        throw new Exception('Insufficient inventory');
    }
});

$crud->getWorkflowEngine()->addHook('after_deliver', function($id, $from, $to, $user) {
    // Send delivery confirmation email
    sendDeliveryEmail($id);
    
    // Update customer loyalty points
    updateLoyaltyPoints($id);
});
```

### Get Workflow History

```php
$history = $crud->getWorkflowHistory($orderId);

foreach ($history as $entry) {
    echo "{$entry['transition']}: {$entry['from_state']} → {$entry['to_state']}";
    echo " by User #{$entry['user_id']} at {$entry['created_at']}";
}
```

### Check Available Transitions

```php
$workflowEngine = $crud->getWorkflowEngine();
$currentState = $workflowEngine->getCurrentState($orderId);
$available = $workflowEngine->getAvailableTransitions($currentState, $user);

foreach ($available as $name => $config) {
    echo "Can execute: {$config['label']} → {$config['to']}";
}
```

## Configuration Options

### Workflow Config

```php
[
    'field' => 'status',                    // Column name for state
    'states' => [...],                      // Array of allowed states
    'transitions' => [...],                 // Transition definitions
    'state_labels' => [...],                // Custom labels and colors
    'history' => true,                      // Enable history tracking
    'history_table' => '_workflow_history'  // History table name
]
```

### Transition Config

```php
[
    'from' => 'pending',              // Single state or array of states
    'to' => 'processing',             // Target state
    'label' => 'Process Order',       // Button label
    'color' => '#3b82f6',             // Button color
    'permissions' => ['admin']        // Required roles (optional)
]
```

### State Label Config

```php
[
    'pending' => [
        'label' => 'Pendiente',
        'color' => '#f59e0b'
    ]
]
```

## Role-Based Permissions

The example includes a role switcher to test different permission levels:

- **admin** - Can execute all transitions
- **manager** - Can process orders
- **warehouse** - Can ship and deliver orders
- **guest** - Cannot execute any transitions

## History Tracking

When `history: true` is enabled, all transitions are logged to `_workflow_history` table with:

- Transition name
- From/to states
- User ID and IP
- Timestamp

## UI Components

### Transition Buttons

Automatically rendered after the form when editing a record:

```
┌─────────────────────────────────────┐
│ Estado actual: [Pending]            │
│ [Process Order] [Cancel]            │
└─────────────────────────────────────┘
```

### State Badges

Colored badges in list views:

```
[Pending] [Processing] [Shipped] [Delivered] [Cancelled]
```

## Use Cases

1. **Order Management** - pending → processing → shipped → delivered
2. **Ticket System** - open → assigned → in_progress → resolved → closed
3. **Content Publishing** - draft → review → approved → published
4. **Approval Workflows** - submitted → pending_approval → approved/rejected
5. **Project Management** - todo → in_progress → review → done

## Advanced Features

### Multiple From States

```php
'cancel' => [
    'from' => ['pending', 'processing'],  // Can cancel from either state
    'to' => 'cancelled'
]
```

### Conditional Transitions

```php
$crud->getWorkflowEngine()->addHook('before_ship', function($id, $from, $to, $user) {
    $order = getOrder($id);
    
    if ($order['payment_status'] !== 'paid') {
        throw new Exception('Cannot ship unpaid order');
    }
    
    if (!hasInventory($order['product'])) {
        throw new Exception('Product out of stock');
    }
});
```

### Notifications on Transition

```php
$crud->getWorkflowEngine()->addHook('after_deliver', function($id, $from, $to, $user) {
    $order = getOrder($id);
    
    // Email customer
    sendEmail($order['customer_email'], 'Order Delivered', '...');
    
    // SMS notification
    sendSMS($order['customer_phone'], 'Your order has been delivered!');
    
    // Webhook
    triggerWebhook('https://api.example.com/order-delivered', $order);
});
```

## Testing

Try different scenarios:

1. **Create new order** - Starts in "pending" state
2. **Switch to manager role** - Can process orders
3. **Switch to warehouse role** - Can ship/deliver but not process
4. **Switch to guest role** - No transition buttons appear
5. **View history** - See complete audit trail

## Notes

- Transitions are validated server-side for security
- Permission checks use current user's role
- Invalid transitions return error message
- History is automatically created on first use
- Hooks can throw exceptions to prevent transitions
