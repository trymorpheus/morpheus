<?php

declare(strict_types=1);

namespace Morpheus\Payment;

class StripeGateway implements PaymentGateway
{
    private string $secretKey;
    private string $apiVersion = '2023-10-16';

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function createPayment(array $data): array
    {
        $response = $this->request('POST', '/v1/payment_intents', [
            'amount' => (int)($data['amount'] * 100),
            'currency' => $data['currency'] ?? 'usd',
            'description' => $data['description'] ?? null,
            'metadata' => $data['metadata'] ?? []
        ]);

        return [
            'success' => isset($response['id']),
            'payment_id' => $response['id'] ?? null,
            'client_secret' => $response['client_secret'] ?? null,
            'status' => $response['status'] ?? 'failed'
        ];
    }

    public function capturePayment(string $paymentId): array
    {
        $response = $this->request('POST', "/v1/payment_intents/{$paymentId}/capture");

        return [
            'success' => $response['status'] === 'succeeded',
            'status' => $response['status'] ?? 'failed'
        ];
    }

    public function refundPayment(string $paymentId, ?float $amount = null): array
    {
        $data = ['payment_intent' => $paymentId];
        if ($amount) $data['amount'] = (int)($amount * 100);

        $response = $this->request('POST', '/v1/refunds', $data);

        return [
            'success' => $response['status'] === 'succeeded',
            'refund_id' => $response['id'] ?? null,
            'status' => $response['status'] ?? 'failed'
        ];
    }

    public function getPaymentStatus(string $paymentId): array
    {
        $response = $this->request('GET', "/v1/payment_intents/{$paymentId}");

        return [
            'success' => isset($response['id']),
            'status' => $response['status'] ?? 'unknown',
            'amount' => isset($response['amount']) ? $response['amount'] / 100 : 0
        ];
    }

    public function createCheckoutSession(array $data): array
    {
        $response = $this->request('POST', '/v1/checkout/sessions', [
            'mode' => 'payment',
            'line_items' => $data['items'],
            'success_url' => $data['success_url'],
            'cancel_url' => $data['cancel_url'],
            'metadata' => $data['metadata'] ?? []
        ]);

        return [
            'success' => isset($response['id']),
            'session_id' => $response['id'] ?? null,
            'url' => $response['url'] ?? null
        ];
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        $ch = curl_init("https://api.stripe.com{$endpoint}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->secretKey}",
                "Stripe-Version: {$this->apiVersion}",
                "Content-Type: application/x-www-form-urlencoded"
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }
}
