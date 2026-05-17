<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Enums\PaymentStatus;
use App\Modules\Billing\Events\PaymentFailed;
use App\Modules\Billing\Events\PaymentReceived;
use App\Modules\Billing\Models\Payment;
use App\Modules\Billing\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

class PaymentService
{
    /**
     * Record a new payment attempt.
     */
    public function recordPayment(
        Subscription $subscription,
        string $transactionId,
        string $gateway,
        int $amount,
        string $currency,
        array $payload = [],
    ): Payment {
        return Payment::create([
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'gateway' => $gateway,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => $currency,
            'status' => PaymentStatus::Pending,
            'payload' => $payload,
        ]);
    }

    /**
     * Find a payment by its gateway transaction ID.
     */
    public function findByTransactionId(string $transactionId): ?Payment
    {
        return Payment::where('transaction_id', $transactionId)->first();
    }

    /**
     * Complete a payment and dispatch events.
     */
    public function completePayment(Payment $payment, ?string $gatewayTransactionId = null): Payment
    {
        $payment->markAsCompleted($gatewayTransactionId);

        PaymentReceived::dispatch($payment, $payment->subscription);

        return $payment->fresh();
    }

    /**
     * Fail a payment and dispatch events.
     */
    public function failPayment(Payment $payment, ?string $errorMessage = null): Payment
    {
        $payment->markAsFailed($errorMessage);

        PaymentFailed::dispatch($payment, $payment->subscription, $errorMessage);

        return $payment->fresh();
    }

    /**
     * Get all payments for a subscription.
     */
    public function getSubscriptionPayments(Subscription $subscription): Collection
    {
        return $subscription->payments()->latest()->get();
    }

    /**
     * Get all payments for a tenant.
     */
    public function getTenantPayments(string $tenantId): Collection
    {
        return Payment::forTenant($tenantId)->latest()->get();
    }

    /**
     * Get the last successful payment for a subscription.
     */
    public function getLastSuccessfulPayment(Subscription $subscription): ?Payment
    {
        return $subscription->payments()
            ->where('status', PaymentStatus::Completed)
            ->latest('paid_at')
            ->first();
    }
}
