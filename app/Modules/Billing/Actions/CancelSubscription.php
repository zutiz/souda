<?php

namespace App\Modules\Billing\Actions;

use App\Modules\Billing\Models\Subscription;
use App\Modules\Billing\Services\SubscriptionService;

class CancelSubscription
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /**
     * Cancel an active subscription.
     */
    public function execute(Subscription $subscription): void
    {
        $this->subscriptionService->cancelSubscription($subscription);
    }
}
