<?php

namespace App\Modules\Billing\DTOs;

class PaymentDTO
{
    public function __construct(
        public readonly string $transactionId,
        public readonly string $gateway,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $status,
        public readonly ?string $checkoutUrl = null,
        public readonly ?string $gatewaySubscriptionId = null,
        public readonly array $payload = [],
        public readonly ?string $message = null,
    ) {}

    /**
     * Create a PaymentDTO from an array (webhook callback data).
     */
    public static function fromArray(array $data): self
    {
        return new self(
            transactionId: $data['transaction_id'] ?? $data['id'] ?? '',
            gateway: $data['gateway'] ?? 'unknown',
            amount: $data['amount'] ?? 0,
            currency: $data['currency'] ?? 'BDT',
            status: $data['status'] ?? 'pending',
            checkoutUrl: $data['checkout_url'] ?? null,
            gatewaySubscriptionId: $data['gateway_subscription_id'] ?? null,
            payload: $data,
            message: $data['message'] ?? $data['error'] ?? null,
        );
    }
}
