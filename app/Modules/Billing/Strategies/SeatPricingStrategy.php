<?php

namespace App\Modules\Billing\Strategies;

use App\Modules\Billing\Contracts\PricingStrategy;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\SeatAllocation;
use App\Modules\Billing\Models\Subscription;

class SeatPricingStrategy implements PricingStrategy
{
    public function calculateAmount(Plan $plan, Subscription $subscription, array $context = []): int
    {
        $baseAmount = $subscription->amount;
        $overage = $this->calculateOverage($subscription->tenant_id, $plan, $subscription);

        return $baseAmount + $overage['overage_amount'];
    }

    public function calculateOverage(string $tenantId, Plan $plan, Subscription $subscription): array
    {
        $totalBillable = SeatAllocation::forTenant($tenantId)
            ->consumed()
            ->whereIn('seat_type', $plan->seatPricingTypes ?? ['owner', 'admin', 'staff'])
            ->count();

        $included = $plan->default_seats ?? 1;
        $overage = max(0, $totalBillable - $included);
        $seatPrice = $plan->seat_price ?? 0;

        return [
            'total_billable' => $totalBillable,
            'included' => $included,
            'overage' => $overage,
            'seat_price' => $seatPrice,
            'overage_amount' => $overage * $seatPrice,
        ];
    }

    public function canAddSeat(string $tenantId, Plan $plan): bool
    {
        $maxSeats = $this->getMaxSeats($plan);

        if ($maxSeats === null) {
            return true;
        }

        $currentSeats = SeatAllocation::forTenant($tenantId)
            ->consumed()
            ->count();

        return $currentSeats < $maxSeats;
    }

    public function getMaxSeats(Plan $plan): ?int
    {
        return $plan->max_seats;
    }
}
