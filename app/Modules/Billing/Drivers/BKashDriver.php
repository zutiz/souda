<?php

namespace App\Modules\Billing\Drivers;

use App\Modules\Billing\Contracts\BillingGatewayInterface;
use App\Modules\Billing\DTOs\PaymentDTO;
use App\Modules\Billing\DTOs\SubscriptionDTO;
use App\Modules\Billing\Exceptions\PaymentFailedException;
use Illuminate\Support\Facades\Log;

class BKashDriver implements BillingGatewayInterface
{
    public function __construct(
        private readonly string $username,
        private readonly string $password,
        private readonly string $appKey,
        private readonly string $appSecret,
        private readonly bool $isSandbox = true,
    ) {}

    public function createPayment(SubscriptionDTO $subscription, array $options = []): PaymentDTO
    {
        // @TODO: Implement bKash tokenized checkout (CreateAgreement -> ExecuteAgreement).
        // Reference: https://developer.bka.sh/display/STD/Tokenized+Checkout
        //
        // Steps:
        // 1. Grant token via POST /tokenized/checkout/v1/token/grant
        // 2. Create agreement via POST /tokenized/checkout/v1/agreements/create
        // 3. Return agreement URL for customer to approve via bKash app

        Log::info('[BKashDriver] createPayment called', [
            'tenant_id' => $subscription->tenantId,
            'amount' => $subscription->amount,
        ]);

        throw new PaymentFailedException(
            message: 'bKash driver — createPayment not yet implemented.',
            gateway: 'bkash',
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentDTO
    {
        // @TODO: Verify bKash payment via POST /tokenized/checkout/v1/payment/status

        Log::info('[BKashDriver] verifyPayment called', [
            'transaction_id' => $transactionId,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'bkash',
            'status' => 'completed',
        ]);
    }

    public function cancelSubscription(string $gatewaySubscriptionId): bool
    {
        // @TODO: Cancel bKash recurring agreement.

        Log::info('[BKashDriver] cancelSubscription called', [
            'gateway_subscription_id' => $gatewaySubscriptionId,
        ]);

        return true;
    }

    public function refundPayment(string $transactionId, ?int $amount = null): PaymentDTO
    {
        // @TODO: Implement refund via bKash API.

        Log::info('[BKashDriver] refundPayment called', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'bkash',
            'status' => 'refunded',
        ]);
    }

    public function generateCheckoutUrl(PaymentDTO $payment): string
    {
        return $payment->checkoutUrl ?? '';
    }
}
