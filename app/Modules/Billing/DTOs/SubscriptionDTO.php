<?php

namespace App\Modules\Billing\DTOs;

use App\Modules\Billing\Enums\BillingCycle;
use App\Modules\Billing\Enums\SubscriptionStatus;

class SubscriptionDTO
{
    public function __construct(
        public readonly string $tenantId,
        public readonly int $planId,
        public readonly string $gateway,
        public readonly SubscriptionStatus $status,
        public readonly BillingCycle $billingCycle,
        public readonly int $amount,
        public readonly string $currency,
        public readonly ?string $gatewaySubscriptionId = null,
        public readonly ?string $startsAt = null,
        public readonly ?string $expiresAt = null,
        public readonly ?string $nextBillingAt = null,
        public readonly ?string $trialEndsAt = null,
        public readonly ?string $cancelledAt = null,
        public readonly array $metadata = [],
    ) {}

    /**
     * Create a SubscriptionDTO from a Subscription model (or array).
     */
    public static function fromModel(object $subscription): self
    {
        return new self(
            tenantId: $subscription->tenant_id,
            planId: $subscription->plan_id,
            gateway: $subscription->gateway,
            status: $subscription->status instanceof SubscriptionStatus
                ? $subscription->status
                : SubscriptionStatus::from($subscription->status),
            billingCycle: $subscription->billing_cycle instanceof BillingCycle
                ? $subscription->billing_cycle
                : BillingCycle::from($subscription->billing_cycle),
            amount: $subscription->amount ?? 0,
            currency: $subscription->currency ?? 'BDT',
            gatewaySubscriptionId: $subscription->gateway_subscription_id,
            startsAt: $subscription->starts_at?->toISOString(),
            expiresAt: $subscription->expires_at?->toISOString(),
            nextBillingAt: $subscription->next_billing_at?->toISOString(),
            trialEndsAt: $subscription->trial_ends_at?->toISOString(),
            cancelledAt: $subscription->cancelled_at?->toISOString(),
            metadata: $subscription->metadata ?? [],
        );
    }
}
