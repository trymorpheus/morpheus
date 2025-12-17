# Payment Gateway Integration

Integración completa de pasarelas de pago (Stripe, PayPal) con Morpheus.

## Características

- ✅ **Múltiples Pasarelas** - Stripe y PayPal con interfaz unificada
- ✅ **Patrón Strategy** - Fácil agregar nuevas pasarelas
- ✅ **Gestión de Estados** - pending → processing → paid → refunded
- ✅ **Captura Manual** - Autorización y captura en dos pasos
- ✅ **Reembolsos** - Reembolsos totales o parciales
- ✅ **Checkout Sessions** - URLs de pago hosteadas
- ✅ **Integración con Morpheus** - CRUD automático para pedidos

## Instalación

```bash
# 1. Ejecutar setup SQL
php -r "$pdo = new PDO('mysql:host=localhost;dbname=test', 'root', 'rootpassword'); $sql = file_get_contents('setup.sql'); $pdo->exec($sql);"

# 2. Configurar credenciales en index.php
# - Stripe: sk_test_YOUR_KEY
# - PayPal: CLIENT_ID y CLIENT_SECRET
```

## Uso

### 1. Registrar Pasarelas

```php
use Morpheus\Payment\PaymentManager;
use Morpheus\Payment\StripeGateway;
use Morpheus\Payment\PayPalGateway;

$paymentManager = new PaymentManager($pdo);

// Stripe
$paymentManager->registerGateway('stripe', new StripeGateway('sk_test_YOUR_KEY'));

// PayPal (sandbox)
$paymentManager->registerGateway('paypal', new PayPalGateway('CLIENT_ID', 'CLIENT_SECRET', true));
```

### 2. Procesar Pago

```php
// Crear payment intent
$result = $paymentManager->processPayment($orderId, 'stripe');

if ($result['success']) {
    echo "Payment ID: {$result['payment_id']}";
    echo "Client Secret: {$result['client_secret']}"; // Para Stripe.js
}
```

### 3. Capturar Pago

```php
// Capturar pago autorizado
$result = $paymentManager->capturePayment($orderId);

if ($result['success']) {
    echo "Pago capturado exitosamente";
}
```

### 4. Reembolsar

```php
// Reembolso total
$result = $paymentManager->refundPayment($orderId);

// Reembolso parcial
$result = $paymentManager->refundPayment($orderId, 50.00);
```

### 5. Checkout Session (Stripe)

```php
$result = $paymentManager->createCheckoutSession($orderId, 'stripe', [
    'success_url' => 'https://example.com/success',
    'cancel_url' => 'https://example.com/cancel'
]);

if ($result['success']) {
    header("Location: {$result['url']}");
}
```

## Arquitectura

### Patrón Strategy

```
PaymentGateway (interface)
    ├── StripeGateway
    ├── PayPalGateway
    └── MercadoPagoGateway (fácil de agregar)

PaymentManager (orchestrator)
    └── Gestiona múltiples gateways
```

### Flujo de Pago

```
1. pending → Pedido creado
2. processing → Pago autorizado (payment_id guardado)
3. paid → Pago capturado
4. refunded → Reembolsado
5. failed → Error en el pago
```

## Agregar Nueva Pasarela

```php
class MercadoPagoGateway implements PaymentGateway
{
    public function createPayment(array $data): array
    {
        // Implementar lógica de MercadoPago
        return ['success' => true, 'payment_id' => '...'];
    }
    
    public function capturePayment(string $paymentId): array { /* ... */ }
    public function refundPayment(string $paymentId, ?float $amount = null): array { /* ... */ }
    public function getPaymentStatus(string $paymentId): array { /* ... */ }
    public function createCheckoutSession(array $data): array { /* ... */ }
}

// Registrar
$paymentManager->registerGateway('mercadopago', new MercadoPagoGateway('ACCESS_TOKEN'));
```

## Integración con Hooks

```php
$crud = new Morpheus($pdo, '30_orders');

// Enviar email cuando se complete el pago
$crud->addHook('afterUpdate', function($data, $id) use ($pdo) {
    if ($data['status'] === 'paid') {
        // Enviar email de confirmación
        mail($data['customer_email'], 'Pago Confirmado', 'Tu pago ha sido procesado');
    }
});
```

## Seguridad

- ✅ **Credenciales en variables de entorno** - No hardcodear keys
- ✅ **HTTPS obligatorio** - Nunca en HTTP
- ✅ **Validación de webhooks** - Verificar firma de eventos
- ✅ **Logs de transacciones** - Auditoría completa
- ✅ **Manejo de errores** - No exponer detalles internos

## Testing

```php
// Usar claves de test
$stripe = new StripeGateway('sk_test_...');
$paypal = new PayPalGateway('CLIENT_ID', 'SECRET', true); // sandbox=true

// Tarjetas de prueba Stripe
// 4242 4242 4242 4242 - Éxito
// 4000 0000 0000 0002 - Decline
```

## Webhooks (Opcional)

```php
// webhook.php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_STRIPE_SIGNATURE'];

// Verificar firma y procesar evento
if (verifySignature($payload, $signature)) {
    $event = json_decode($payload, true);
    
    if ($event['type'] === 'payment_intent.succeeded') {
        $paymentId = $event['data']['object']['id'];
        // Actualizar orden
    }
}
```

## Recursos

- [Stripe API Docs](https://stripe.com/docs/api)
- [PayPal API Docs](https://developer.paypal.com/docs/api/overview/)
- [Morpheus Hooks](../../docs/HOOKS.md)
- [Morpheus Workflows](../../docs/WORKFLOW.md)
