<?php

namespace App\Modules\Billing\Actions;

use App\Modules\Billing\Enums\BillingCycle;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Billing\Services\PlanService;
use App\Modules\Billing\Services\SubscriptionService;

class CreateSubscription
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly PlanService $planService,
    ) {}

    /**
     * Create a new subscription for a tenant.
     *
     * @return array{subscription: Subscription, checkoutUrl: ?string}
     */
    public function execute(
        string $tenantId,
        int $planId,
        string $gateway,
        ?string $billingCycle = null,
        ?array $options = [],
    ): array {
        $plan = $this->planService->findOrFail($planId);

        // Validate the gateway is available.
        $availableGateways = config('billing.gateways', []);
        if (! isset($availableGateways[$gateway])) {
            throw new \InvalidArgumentException("Gateway '{$gateway}' is not configured.");
        }

        $billingCycle = $billingCycle
            ? BillingCycle::tryFrom($billingCycle)
            : BillingCycle::Monthly;

        if (! $billingCycle) {
            throw new \InvalidArgumentException("Invalid billing cycle: {$billingCycle}");
        }

        return $this->subscriptionService->createSubscription(
            tenantId: $tenantId,
            planId: $planId,
            gateway: $gateway,
            billingCycle: $billingCycle,
            options: $options,
        );
    }
}
