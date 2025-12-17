<?php

declare(strict_types=1);

namespace Morpheus\Payment;

class PayPalGateway implements PaymentGateway
{
    private string $clientId;
    private string $clientSecret;
    private bool $sandbox;
    private ?string $accessToken = null;

    public function __construct(string $clientId, string $clientSecret, bool $sandbox = true)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->sandbox = $sandbox;
    }

    public function createPayment(array $data): array
    {
        $response = $this->request('POST', '/v2/checkout/orders', [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => $data['currency'] ?? 'USD',
                    'value' => number_format($data['amount'], 2, '.', '')
                ],
                'description' => $data['description'] ?? null
            ]]
        ]);

        return [
            'success' => isset($response['id']),
            'payment_id' => $response['id'] ?? null,
            'status' => $response['status'] ?? 'failed'
        ];
    }

    public function capturePayment(string $paymentId): array
    {
        $response = $this->request('POST', "/v2/checkout/orders/{$paymentId}/capture");

        return [
            'success' => $response['status'] === 'COMPLETED',
            'status' => $response['status'] ?? 'failed'
        ];
    }

    public function refundPayment(string $paymentId, ?float $amount = null): array
    {
        $data = $amount ? ['amount' => ['value' => number_format($amount, 2, '.', ''), 'currency_code' => 'USD']] : [];
        $response = $this->request('POST', "/v2/payments/captures/{$paymentId}/refund", $data);

        return [
            'success' => $response['status'] === 'COMPLETED',
            'refund_id' => $response['id'] ?? null,
            'status' => $response['status'] ?? 'failed'
        ];
    }

    public function getPaymentStatus(string $paymentId): array
    {
        $response = $this->request('GET', "/v2/checkout/orders/{$paymentId}");

        return [
            'success' => isset($response['id']),
            'status' => $response['status'] ?? 'unknown',
            'amount' => $response['purchase_units'][0]['amount']['value'] ?? 0
        ];
    }

    public function createCheckoutSession(array $data): array
    {
        return $this->createPayment($data);
    }

    private function getAccessToken(): string
    {
        if ($this->accessToken) return $this->accessToken;

        $ch = curl_init($this->getBaseUrl() . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_USERPWD => "{$this->clientId}:{$this->clientSecret}",
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        $this->accessToken = $response['access_token'] ?? '';
        return $this->accessToken;
    }

    private function request(string $method, string $endpoint, array $data = []): array
    {
        $ch = curl_init($this->getBaseUrl() . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$this->getAccessToken()}",
                "Content-Type: application/json"
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    private function getBaseUrl(): string
    {
        return $this->sandbox ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }
}
