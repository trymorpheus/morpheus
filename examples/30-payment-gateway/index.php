<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Morpheus\Morpheus;
use Morpheus\Payment\PaymentManager;
use Morpheus\Payment\StripeGateway;
use Morpheus\Payment\PayPalGateway;

$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Configure payment gateways
$paymentManager = new PaymentManager($pdo);
$paymentManager->registerGateway('stripe', new StripeGateway('sk_test_YOUR_KEY'))
               ->registerGateway('paypal', new PayPalGateway('CLIENT_ID', 'CLIENT_SECRET', true));

// Handle payment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $orderId = (int)$_POST['order_id'];
    
    $result = match($_POST['action']) {
        'process' => $paymentManager->processPayment($orderId, $_POST['gateway']),
        'capture' => $paymentManager->capturePayment($orderId),
        'refund' => $paymentManager->refundPayment($orderId),
        'checkout' => $paymentManager->createCheckoutSession($orderId, $_POST['gateway'], [
            'success_url' => 'http://localhost/examples/30-payment-gateway/success.php',
            'cancel_url' => 'http://localhost/examples/30-payment-gateway/index.php'
        ]),
        default => ['success' => false, 'error' => 'Invalid action']
    };
    
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// CRUD for orders
$crud = new Morpheus($pdo, '30_orders');

// Hook to prevent manual status changes
$crud->addHook('beforeSave', function($data) {
    if (isset($data['status']) && $data['status'] !== 'pending') {
        unset($data['status']); // Status only changes via payment actions
    }
    return $data;
});

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Gateway - Morpheus</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f7fa; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; }
        h1 { color: #2d3748; margin-bottom: 10px; }
        .subtitle { color: #718096; margin-bottom: 30px; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .orders-list { display: grid; gap: 15px; }
        .order-item { border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .order-info h3 { color: #2d3748; margin-bottom: 5px; }
        .order-info p { color: #718096; font-size: 14px; }
        .order-actions { display: flex; gap: 10px; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; transition: all 0.2s; }
        .btn-stripe { background: #635bff; color: white; }
        .btn-stripe:hover { background: #4f46e5; }
        .btn-paypal { background: #0070ba; color: white; }
        .btn-paypal:hover { background: #005ea6; }
        .btn-capture { background: #10b981; color: white; }
        .btn-capture:hover { background: #059669; }
        .btn-refund { background: #ef4444; color: white; }
        .btn-refund:hover { background: #dc2626; }
        .status { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        .status-refunded { background: #e5e7eb; color: #374151; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
    </style>
</head>
<body>
    <div class="container">
        <h1>💳 Payment Gateway Integration</h1>
        <p class="subtitle">Integración con Stripe y PayPal usando Morpheus</p>

        <div id="alert" style="display: none;"></div>

        <div class="card">
            <h2 style="margin-bottom: 15px;">Pedidos Pendientes</h2>
            <div class="orders-list">
                <?php
                $stmt = $pdo->query("SELECT * FROM 30_orders ORDER BY created_at DESC");
                while ($order = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <div class="order-item">
                    <div class="order-info">
                        <h3><?= htmlspecialchars($order['customer_name']) ?> - $<?= number_format($order['total'], 2) ?></h3>
                        <p>
                            <?= htmlspecialchars($order['customer_email']) ?> | 
                            <span class="status status-<?= $order['status'] ?>"><?= strtoupper($order['status']) ?></span>
                            <?php if ($order['payment_method']): ?>
                                | <?= strtoupper($order['payment_method']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="order-actions">
                        <?php if ($order['status'] === 'pending'): ?>
                            <button class="btn btn-stripe" onclick="processPayment(<?= $order['id'] ?>, 'stripe')">Pagar con Stripe</button>
                            <button class="btn btn-paypal" onclick="processPayment(<?= $order['id'] ?>, 'paypal')">Pagar con PayPal</button>
                        <?php elseif ($order['status'] === 'processing'): ?>
                            <button class="btn btn-capture" onclick="capturePayment(<?= $order['id'] ?>)">Capturar Pago</button>
                        <?php elseif ($order['status'] === 'paid'): ?>
                            <button class="btn btn-refund" onclick="refundPayment(<?= $order['id'] ?>)">Reembolsar</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 15px;">Crear Nuevo Pedido</h2>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
                $result = $crud->handleSubmission();
                if ($result['success']) {
                    echo '<div class="alert alert-success">✓ Pedido creado correctamente</div>';
                    echo '<script>setTimeout(() => location.reload(), 1000);</script>';
                }
            }
            echo $crud->renderForm();
            ?>
        </div>
    </div>

    <script>
        function showAlert(message, type) {
            const alert = document.getElementById('alert');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            alert.style.display = 'block';
            setTimeout(() => alert.style.display = 'none', 5000);
        }

        async function processPayment(orderId, gateway) {
            const formData = new FormData();
            formData.append('action', 'process');
            formData.append('order_id', orderId);
            formData.append('gateway', gateway);

            const response = await fetch('', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                showAlert(`✓ Pago iniciado con ${gateway}. ID: ${result.payment_id}`, 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert(`✗ Error: ${result.error}`, 'error');
            }
        }

        async function capturePayment(orderId) {
            const formData = new FormData();
            formData.append('action', 'capture');
            formData.append('order_id', orderId);

            const response = await fetch('', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                showAlert('✓ Pago capturado exitosamente', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert(`✗ Error: ${result.error}`, 'error');
            }
        }

        async function refundPayment(orderId) {
            if (!confirm('¿Estás seguro de reembolsar este pago?')) return;

            const formData = new FormData();
            formData.append('action', 'refund');
            formData.append('order_id', orderId);

            const response = await fetch('', { method: 'POST', body: formData });
            const result = await response.json();

            if (result.success) {
                showAlert('✓ Reembolso procesado exitosamente', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showAlert(`✗ Error: ${result.error}`, 'error');
            }
        }
    </script>
</body>
</html>
