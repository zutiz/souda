<?php

namespace App\Modules\Billing\Actions;

use App\Modules\Billing\Models\Subscription;
use App\Modules\Billing\Services\SubscriptionService;

class ActivateSubscription
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /**
     * Activate a subscription (move from pending_payment/trial to active).
     */
    public function execute(Subscription $subscription, bool $wasTrial = false): void
    {
        $this->subscriptionService->activateSubscription($subscription, $wasTrial);
    }
}
