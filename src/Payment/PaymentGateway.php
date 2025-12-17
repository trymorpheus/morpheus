<?php

declare(strict_types=1);

namespace Morpheus\Payment;

interface PaymentGateway
{
    public function createPayment(array $data): array;
    public function capturePayment(string $paymentId): array;
    public function refundPayment(string $paymentId, ?float $amount = null): array;
    public function getPaymentStatus(string $paymentId): array;
    public function createCheckoutSession(array $data): array;
}
