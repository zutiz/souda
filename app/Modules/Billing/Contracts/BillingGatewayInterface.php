<?php

namespace App\Modules\Billing\Contracts;

use App\Modules\Billing\DTOs\PaymentDTO;
use App\Modules\Billing\DTOs\SubscriptionDTO;
use App\Modules\Billing\Exceptions\PaymentFailedException;

interface BillingGatewayInterface
{
    /**
     * Create a payment for a subscription.
     *
     * @param  SubscriptionDTO  $subscription  The subscription being paid for.
     * @param  array  $options  Gateway-specific options (e.g., success/cancel URLs).
     *
     * @throws PaymentFailedException
     */
    public function createPayment(SubscriptionDTO $subscription, array $options = []): PaymentDTO;

    /**
     * Verify a payment callback or webhook payload.
     *
     * @param  string  $transactionId  The gateway transaction ID.
     * @param  array  $payload  Raw gateway response data.
     */
    public function verifyPayment(string $transactionId, array $payload = []): PaymentDTO;

    /**
     * Cancel an active subscription at the gateway level.
     *
     * @param  string  $gatewaySubscriptionId  The subscription ID from the gateway.
     */
    public function cancelSubscription(string $gatewaySubscriptionId): bool;

    /**
     * Process a refund for a given transaction.
     *
     * @param  string  $transactionId  The gateway transaction ID.
     * @param  int|null  $amount  Amount in cents (null = full refund).
     */
    public function refundPayment(string $transactionId, ?int $amount = null): PaymentDTO;

    /**
     * Generate a checkout/redirect URL for the customer.
     *
     * @param  PaymentDTO  $payment  The payment to generate a URL for.
     */
    public function generateCheckoutUrl(PaymentDTO $payment): string;
}
