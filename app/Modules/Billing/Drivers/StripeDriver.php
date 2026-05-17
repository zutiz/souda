<?php

namespace App\Modules\Billing\Drivers;

use App\Modules\Billing\Contracts\BillingGatewayInterface;
use App\Modules\Billing\DTOs\PaymentDTO;
use App\Modules\Billing\DTOs\SubscriptionDTO;
use App\Modules\Billing\Exceptions\PaymentFailedException;
use Illuminate\Support\Facades\Log;

class StripeDriver implements BillingGatewayInterface
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $webhookSecret,
    ) {}

    public function createPayment(SubscriptionDTO $subscription, array $options = []): PaymentDTO
    {
        // @TODO: Implement Stripe Checkout Session creation.
        // Example:
        //   $stripe = new \Stripe\StripeClient($this->secretKey);
        //   $session = $stripe->checkout->sessions->create([...]);

        Log::info('[StripeDriver] createPayment called', [
            'subscription' => $subscription->tenantId,
            'amount' => $subscription->amount,
        ]);

        throw new PaymentFailedException(
            message: 'Stripe driver not fully implemented.',
            gateway: 'stripe',
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentDTO
    {
        // @TODO: Verify Stripe webhook signature and retrieve session.
        // $event = \Stripe\Webhook::constructEvent($payload['raw_body'], $payload['sig_header'], $this->webhookSecret);

        Log::info('[StripeDriver] verifyPayment called', [
            'transaction_id' => $transactionId,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'stripe',
            'status' => 'completed',
        ]);
    }

    public function cancelSubscription(string $gatewaySubscriptionId): bool
    {
        // @TODO: Cancel at Stripe via API.
        // $stripe = new \Stripe\StripeClient($this->secretKey);
        // $stripe->subscriptions->cancel($gatewaySubscriptionId, []);

        Log::info('[StripeDriver] cancelSubscription called', [
            'gateway_subscription_id' => $gatewaySubscriptionId,
        ]);

        return true;
    }

    public function refundPayment(string $transactionId, ?int $amount = null): PaymentDTO
    {
        // @TODO: Create Stripe refund.

        Log::info('[StripeDriver] refundPayment called', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'stripe',
            'status' => 'refunded',
        ]);
    }

    public function generateCheckoutUrl(PaymentDTO $payment): string
    {
        // @TODO: Return the Stripe Checkout Session URL from createPayment.
        return $payment->checkoutUrl ?? '';
    }
}
