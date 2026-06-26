<?php

namespace App\Modules\Billing\Drivers;

use App\Modules\Billing\Contracts\BillingGatewayInterface;
use App\Modules\Billing\DTOs\PaymentDTO;
use App\Modules\Billing\DTOs\SubscriptionDTO;
use App\Modules\Billing\Exceptions\PaymentFailedException;
use Illuminate\Support\Facades\Log;

class NagadDriver implements BillingGatewayInterface
{
    public function __construct(
        private readonly string $merchantId,
        private readonly string $publicKey,
        private readonly string $privateKey,
        private readonly bool $isSandbox = true,
    ) {}

    public function createPayment(SubscriptionDTO $subscription, array $options = []): PaymentDTO
    {
        // @TODO: Implement Nagad payment initiation.
        // Reference: https://developer.nagad.com/
        //
        // Steps:
        // 1. Generate sensitive data and signature
        // 2. POST to /api/dfs/check-out/initialize/{merchantId}/{orderId}
        // 3. Complete payment via /api/dfs/check-out/complete/{paymentRefId}

        Log::info('[NagadDriver] createPayment called', [
            'tenant_id' => $subscription->tenantId,
            'amount' => $subscription->amount,
        ]);

        throw new PaymentFailedException(
            message: 'Nagad driver — createPayment not yet implemented.',
            gateway: 'nagad',
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentDTO
    {
        // @TODO: Verify Nagad payment status.

        Log::info('[NagadDriver] verifyPayment called', [
            'transaction_id' => $transactionId,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'nagad',
            'status' => 'completed',
        ]);
    }

    public function cancelSubscription(string $gatewaySubscriptionId): bool
    {
        // Nagad does not natively support recurring subscriptions.
        Log::info('[NagadDriver] cancelSubscription called - no remote action needed');

        return true;
    }

    public function refundPayment(string $transactionId, ?int $amount = null): PaymentDTO
    {
        // @TODO: Implement Nagad refund.

        Log::info('[NagadDriver] refundPayment called', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'nagad',
            'status' => 'refunded',
        ]);
    }

    public function generateCheckoutUrl(PaymentDTO $payment): string
    {
        return $payment->checkoutUrl ?? '';
    }
}
