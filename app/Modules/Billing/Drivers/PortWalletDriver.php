<?php

namespace App\Modules\Billing\Drivers;

use App\Modules\Billing\Contracts\BillingGatewayInterface;
use App\Modules\Billing\DTOs\PaymentDTO;
use App\Modules\Billing\DTOs\SubscriptionDTO;
use App\Modules\Billing\Exceptions\PaymentFailedException;
use Illuminate\Support\Facades\Log;

class PortWalletDriver implements BillingGatewayInterface
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $apiSecret,
        private readonly bool $isSandbox = true,
    ) {}

    public function createPayment(SubscriptionDTO $subscription, array $options = []): PaymentDTO
    {
        // @TODO: Implement PortWallet payment initiation.
        // Reference: https://www.portwallet.com/
        //
        // POST to https://api.portwallet.com/payment/v2/init
        // with amount, currency, callback URLs, etc.

        Log::info('[PortWalletDriver] createPayment called', [
            'tenant_id' => $subscription->tenantId,
            'amount' => $subscription->amount,
        ]);

        throw new PaymentFailedException(
            message: 'PortWallet driver — createPayment not yet implemented.',
            gateway: 'portwallet',
        );
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentDTO
    {
        // @TODO: Verify PortWallet transaction status.

        Log::info('[PortWalletDriver] verifyPayment called', [
            'transaction_id' => $transactionId,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'portwallet',
            'status' => 'completed',
        ]);
    }

    public function cancelSubscription(string $gatewaySubscriptionId): bool
    {
        Log::info('[PortWalletDriver] cancelSubscription called - no remote action needed');

        return true;
    }

    public function refundPayment(string $transactionId, ?int $amount = null): PaymentDTO
    {
        // @TODO: Implement PortWallet refund.

        Log::info('[PortWalletDriver] refundPayment called', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'portwallet',
            'status' => 'refunded',
        ]);
    }

    public function generateCheckoutUrl(PaymentDTO $payment): string
    {
        return $payment->checkoutUrl ?? '';
    }
}
