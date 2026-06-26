<?php

namespace App\Modules\Billing\Drivers;

use App\Modules\Billing\Contracts\BillingGatewayInterface;
use App\Modules\Billing\DTOs\PaymentDTO;
use App\Modules\Billing\DTOs\SubscriptionDTO;
use App\Modules\Billing\Exceptions\PaymentFailedException;

/**
 * @deprecated This driver is a stub and not implemented.
 *             Do not use in production. All methods will throw
 *             PaymentFailedException until Stripe integration is built.
 */
class StripeDriver implements BillingGatewayInterface
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $webhookSecret,
    ) {}

    public function createPayment(SubscriptionDTO $subscription, array $options = []): never
    {
        throw new PaymentFailedException(
            message: 'Stripe driver is not implemented.',
            gateway: 'stripe',
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): never
    {
        throw new PaymentFailedException(
            message: 'Stripe driver is not implemented.',
            gateway: 'stripe',
        );
    }

    public function cancelSubscription(string $gatewaySubscriptionId): never
    {
        throw new PaymentFailedException(
            message: 'Stripe driver is not implemented.',
            gateway: 'stripe',
        );
    }

    public function refundPayment(string $transactionId, ?int $amount = null): never
    {
        throw new PaymentFailedException(
            message: 'Stripe driver is not implemented.',
            gateway: 'stripe',
        );
    }

    public function generateCheckoutUrl(PaymentDTO $payment): string
    {
        throw new PaymentFailedException(
            message: 'Stripe driver is not implemented.',
            gateway: 'stripe',
        );
    }
}
