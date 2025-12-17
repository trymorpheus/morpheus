<?php

declare(strict_types=1);

namespace Morpheus\Payment;

use PDO;

class PaymentManager
{
    private PDO $pdo;
    private array $gateways = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function registerGateway(string $name, PaymentGateway $gateway): self
    {
        $this->gateways[$name] = $gateway;
        return $this;
    }

    public function processPayment(int $orderId, string $gateway): array
    {
        if (!isset($this->gateways[$gateway])) {
            return ['success' => false, 'error' => 'Gateway not found'];
        }

        $order = $this->getOrder($orderId);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found'];
        }

        $result = $this->gateways[$gateway]->createPayment([
            'amount' => $order['total'],
            'currency' => 'USD',
            'description' => "Order #{$orderId}",
            'metadata' => ['order_id' => $orderId]
        ]);

        if ($result['success']) {
            $this->updateOrder($orderId, [
                'payment_method' => $gateway,
                'payment_id' => $result['payment_id'],
                'payment_status' => $result['status'],
                'status' => 'processing'
            ]);
        }

        return $result;
    }

    public function capturePayment(int $orderId): array
    {
        $order = $this->getOrder($orderId);
        if (!$order || !$order['payment_id']) {
            return ['success' => false, 'error' => 'Invalid order'];
        }

        $gateway = $this->gateways[$order['payment_method']] ?? null;
        if (!$gateway) {
            return ['success' => false, 'error' => 'Gateway not found'];
        }

        $result = $gateway->capturePayment($order['payment_id']);

        if ($result['success']) {
            $this->updateOrder($orderId, [
                'payment_status' => $result['status'],
                'status' => 'paid'
            ]);
        }

        return $result;
    }

    public function refundPayment(int $orderId, ?float $amount = null): array
    {
        $order = $this->getOrder($orderId);
        if (!$order || !$order['payment_id']) {
            return ['success' => false, 'error' => 'Invalid order'];
        }

        $gateway = $this->gateways[$order['payment_method']] ?? null;
        if (!$gateway) {
            return ['success' => false, 'error' => 'Gateway not found'];
        }

        $result = $gateway->refundPayment($order['payment_id'], $amount);

        if ($result['success']) {
            $this->updateOrder($orderId, [
                'payment_status' => $result['status'],
                'status' => 'refunded'
            ]);
        }

        return $result;
    }

    public function createCheckoutSession(int $orderId, string $gateway, array $urls): array
    {
        if (!isset($this->gateways[$gateway])) {
            return ['success' => false, 'error' => 'Gateway not found'];
        }

        $order = $this->getOrder($orderId);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found'];
        }

        $result = $this->gateways[$gateway]->createCheckoutSession([
            'items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => "Order #{$orderId}"],
                    'unit_amount' => (int)($order['total'] * 100)
                ],
                'quantity' => 1
            ]],
            'success_url' => $urls['success_url'],
            'cancel_url' => $urls['cancel_url'],
            'metadata' => ['order_id' => $orderId]
        ]);

        if ($result['success']) {
            $this->updateOrder($orderId, [
                'payment_method' => $gateway,
                'payment_id' => $result['session_id'] ?? $result['payment_id']
            ]);
        }

        return $result;
    }

    private function getOrder(int $orderId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM orders WHERE id = :id");
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetch() ?: null;
    }

    private function updateOrder(int $orderId, array $data): void
    {
        $sets = [];
        foreach ($data as $key => $value) {
            $sets[] = "{$key} = :{$key}";
        }

        $sql = "UPDATE orders SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge($data, ['id' => $orderId]));
    }
}
