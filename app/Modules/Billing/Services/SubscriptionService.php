<?php

namespace App\Modules\Billing\Services;

use App\Models\Tenant;
use App\Modules\Billing\DTOs\SubscriptionDTO;
use App\Modules\Billing\Enums\BillingCycle;
use App\Modules\Billing\Enums\PaymentStatus;
use App\Modules\Billing\Enums\SubscriptionStatus;
use App\Modules\Billing\Events\PaymentReceived;
use App\Modules\Billing\Events\SubscriptionActivated;
use App\Modules\Billing\Events\SubscriptionCancelled;
use App\Modules\Billing\Exceptions\PaymentFailedException;
use App\Modules\Billing\Models\Subscription;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    public function __construct(
        private readonly BillingManager $billingManager,
        private readonly PaymentService $paymentService,
        private readonly PlanService $planService,
    ) {}

    /**
     * Create a new subscription for a tenant.
     *
     * Creates the subscription record in pending_payment status, then
     * initiates payment via the selected gateway.
     *
     * @return array{subscription: Subscription, checkoutUrl: ?string}
     */
    public function createSubscription(
        string $tenantId,
        int $planId,
        string $gateway,
        ?BillingCycle $billingCycle = null,
        ?array $options = [],
    ): array {
        $plan = $this->planService->findOrFail($planId);

        $billingCycle = $billingCycle ?? BillingCycle::Monthly;

        $amount = $billingCycle === BillingCycle::Yearly
            ? ($plan->yearly_price ?? $plan->monthly_price * 12)
            : $plan->monthly_price;

        $tenant = Tenant::find($tenantId);
        $trialAvailable = $plan->trial_enabled && $tenant && ! $tenant->trial_used;

        if ($trialAvailable && $plan->trial_without_card) {
            $status = SubscriptionStatus::Trial;
        } else {
            $status = SubscriptionStatus::PendingPayment;
        }

        $now = now();
        $trialEndsAt = $trialAvailable
            ? $now->copy()->addDays($plan->trial_days)
            : null;

        $expiresAt = match ($status) {
            SubscriptionStatus::Trial => $trialEndsAt,
            default => null,
        };

        $subscription = Subscription::create([
            'tenant_id' => $tenantId,
            'plan_id' => $plan->id,
            'gateway' => $gateway,
            'status' => $status,
            'billing_cycle' => $billingCycle,
            'amount' => $amount,
            'currency' => $plan->currency,
            'starts_at' => $now,
            'expires_at' => $expiresAt,
            'trial_ends_at' => $trialEndsAt,
            'next_billing_at' => $expiresAt,
            'metadata' => $options['metadata'] ?? [],
        ]);

        Log::info('Subscription created', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'gateway' => $gateway,
        ]);

        // If amount is zero (free plan), activate immediately without payment.
        if ($amount === 0) {
            $this->activateSubscription($subscription);

            return [
                'subscription' => $subscription,
                'checkoutUrl' => null,
            ];
        }

        // Trial with payment: if the user selected a gateway and there's an amount due,
        // process the payment through the gateway. The trial info (trial_ends_at) is
        // preserved on the subscription for downstream handling.
        if ($status === SubscriptionStatus::Trial) {
            $subscription->update(['status' => SubscriptionStatus::PendingPayment]);
        }

        // Initiate payment via gateway.
        $subscriptionDTO = SubscriptionDTO::fromModel($subscription);

        try {
            $gatewayDriver = $this->billingManager->driver($gateway);
            $paymentDTO = $gatewayDriver->createPayment($subscriptionDTO, $options);

            // Record the payment.
            $payment = $this->paymentService->recordPayment(
                subscription: $subscription,
                transactionId: $paymentDTO->transactionId,
                gateway: $gateway,
                amount: $amount,
                currency: $plan->currency,
                payload: $paymentDTO->payload,
            );

            return [
                'subscription' => $subscription,
                'checkoutUrl' => $paymentDTO->checkoutUrl,
            ];
        } catch (\Throwable $e) {
            Log::error('Payment initiation failed', [
                'subscription_id' => $subscription->id,
                'gateway' => $gateway,
                'error' => $e->getMessage(),
            ]);

            $subscription->update(['status' => SubscriptionStatus::PendingPayment]);

            throw $e;
        }
    }

    /**
     * Activate a subscription (transition to active status).
     */
    public function activateSubscription(Subscription $subscription, bool $wasTrial = false): void
    {
        $plan = $subscription->plan;
        $now = now();

        $expiresAt = match ($subscription->billing_cycle) {
            BillingCycle::Daily => $now->copy()->addDay(),
            BillingCycle::Weekly => $now->copy()->addWeek(),
            BillingCycle::Monthly => $now->copy()->addMonth(),
            BillingCycle::Quarterly => $now->copy()->addMonths(3),
            BillingCycle::Yearly => $now->copy()->addYear(),
            BillingCycle::Custom => $now->copy()->addDays($subscription->metadata['billing_days'] ?? 30),
        };

        $subscription->update([
            'status' => SubscriptionStatus::Active,
            'starts_at' => $now,
            'expires_at' => $expiresAt,
            'next_billing_at' => $expiresAt,
        ]);

        if ($wasTrial || $subscription->trial_ends_at) {
            $subscription->tenant->update(['trial_used' => true]);
        }

        SubscriptionActivated::dispatch($subscription, $wasTrial);
    }

    /**
     * Verify a payment callback and activate the subscription if successful.
     */
    public function verifyAndActivate(string $transactionId, string $gateway, array $payload = []): Subscription
    {
        $gatewayDriver = $this->billingManager->driver($gateway);
        $paymentDTO = $gatewayDriver->verifyPayment($transactionId, $payload);

        if ($paymentDTO->status === 'failed') {
            throw new PaymentFailedException(
                message: 'Payment verification failed: '.($paymentDTO->message ?? 'Gateway returned failure status'),
                gateway: $gateway,
                transactionId: $transactionId,
            );
        }

        $payment = $this->paymentService->findByTransactionId($transactionId);
        $subscription = $payment?->subscription;

        if (! $subscription) {
            throw new PaymentFailedException(
                message: "No subscription found for transaction: {$transactionId}",
                gateway: $gateway,
                transactionId: $transactionId,
            );
        }

        if ($payment->status === PaymentStatus::Completed) {
            return $subscription;
        }

        $payment->markAsCompleted($paymentDTO->transactionId);

        try {
            PaymentReceived::dispatch($payment, $subscription);
        } catch (\Throwable $e) {
            Log::warning('PaymentReceived event failed, but payment is already marked completed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $this->activateSubscription($subscription);
        } catch (\Throwable $e) {
            Log::warning('Subscription activation event failed, but subscription is already active', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $subscription->fresh();
    }

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(Subscription $subscription): void
    {
        if ($subscription->status === SubscriptionStatus::Cancelled) {
            return;
        }

        // Notify the gateway if there's a remote subscription to cancel.
        if ($subscription->gateway !== 'manual' && $subscription->gateway_subscription_id) {
            try {
                $gatewayDriver = $this->billingManager->driver($subscription->gateway);
                $gatewayDriver->cancelSubscription($subscription->gateway_subscription_id);
            } catch (\Throwable $e) {
                Log::warning('Gateway cancellation failed (subscription still cancelled locally)', [
                    'subscription_id' => $subscription->id,
                    'gateway' => $subscription->gateway,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $subscription->markAsCancelled();

        SubscriptionCancelled::dispatch($subscription);
    }

    /**
     * Check if a tenant has an accessible subscription.
     */
    public function tenantHasAccessibleSubscription(string $tenantId): bool
    {
        return Subscription::forTenant($tenantId)
            ->accessible()
            ->exists();
    }

    /**
     * Get the current accessible subscription for a tenant.
     */
    public function getTenantSubscription(string $tenantId): ?Subscription
    {
        return Subscription::forTenant($tenantId)
            ->accessible()
            ->latest('id')
            ->first();
    }

    /**
     * Check if a tenant has access to a specific feature.
     */
    public function tenantHasFeature(string $tenantId, string $feature): bool
    {
        $subscription = $this->getTenantSubscription($tenantId);

        if (! $subscription) {
            return false;
        }

        $plan = $subscription->plan;

        if (! $plan) {
            return false;
        }

        $features = $plan->features ?? [];

        return in_array($feature, $features, true);
    }

    /**
     * Get the feature limits for a tenant's subscription.
     */
    public function getTenantFeatureLimits(string $tenantId, string $feature): ?int
    {
        $subscription = $this->getTenantSubscription($tenantId);

        if (! $subscription || ! $subscription->plan) {
            return null;
        }

        $limits = $subscription->plan->limits ?? [];

        return $limits[$feature] ?? null;
    }

    /**
     * Check if a tenant has met/exceeded a feature's limit.
     */
    public function tenantHasReachedLimit(string $tenantId, string $feature, int $currentUsage): bool
    {
        $limit = $this->getTenantFeatureLimits($tenantId, $feature);

        // No limit defined means unlimited.
        if ($limit === null) {
            return false;
        }

        return $currentUsage >= $limit;
    }
}
