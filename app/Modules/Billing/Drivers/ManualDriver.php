<?php

namespace App\Modules\Billing\Drivers;

use App\Modules\Billing\Contracts\BillingGatewayInterface;
use App\Modules\Billing\DTOs\PaymentDTO;
use App\Modules\Billing\DTOs\SubscriptionDTO;
use App\Modules\Billing\Enums\PaymentStatus;
use Illuminate\Support\Facades\Log;

class ManualDriver implements BillingGatewayInterface
{
    /**
     * Manual driver handles invoice-based payments (bank transfer, cash, etc.).
     * No actual gateway integration — payments are verified manually by admins.
     */
    public function createPayment(SubscriptionDTO $subscription, array $options = []): PaymentDTO
    {
        // Manual payments always return "pending" — an admin must confirm the payment.
        $transactionId = $options['transaction_id'] ?? 'manual_'.uniqid();

        Log::info('[ManualDriver] createPayment called - pending manual verification', [
            'tenant_id' => $subscription->tenantId,
            'transaction_id' => $transactionId,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'manual',
            'status' => PaymentStatus::Pending->value,
            'amount' => $subscription->amount,
            'currency' => $subscription->currency,
            'checkout_url' => $options['checkout_url'] ?? null,
        ]);
    }

    public function verifyPayment(string $transactionId, array $payload = []): PaymentDTO
    {
        // Manual payments are verified by admin action, not by gateway.
        // This is a no-op for the driver.
        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'manual',
            'status' => $payload['status'] ?? PaymentStatus::Pending->value,
        ]);
    }

    public function cancelSubscription(string $gatewaySubscriptionId): bool
    {
        Log::info('[ManualDriver] cancelSubscription called - no remote action needed');

        return true;
    }

    public function refundPayment(string $transactionId, ?int $amount = null): PaymentDTO
    {
        Log::info('[ManualDriver] refundPayment called - manual refund processing required', [
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        return PaymentDTO::fromArray([
            'transaction_id' => $transactionId,
            'gateway' => 'manual',
            'status' => PaymentStatus::Refunded->value,
        ]);
    }

    public function generateCheckoutUrl(PaymentDTO $payment): string
    {
        return $payment->checkoutUrl ?? '';
    }
}
