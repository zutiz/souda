<?php

namespace App\Modules\Billing\Strategies;

use App\Modules\Billing\Contracts\PricingStrategy;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;

class FlatPricingStrategy implements PricingStrategy
{
    public function calculateAmount(Plan $plan, Subscription $subscription, array $context = []): int
    {
        return $subscription->amount;
    }

    public function calculateOverage(string $tenantId, Plan $plan, Subscription $subscription): array
    {
        return [
            'total_billable' => 0,
            'included' => 0,
            'overage' => 0,
            'seat_price' => 0,
            'overage_amount' => 0,
        ];
    }

    public function canAddSeat(string $tenantId, Plan $plan): bool
    {
        return true;
    }

    public function getMaxSeats(Plan $plan): ?int
    {
        return null;
    }
}
