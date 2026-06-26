<?php

namespace App\Modules\Billing\Contracts;

use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;

interface PricingStrategy
{
    /**
     * Calculate the total amount for a billing cycle.
     */
    public function calculateAmount(Plan $plan, Subscription $subscription, array $context = []): int;

    /**
     * Calculate seat overage for a tenant.
     *
     * @return array{total_billable: int, included: int, overage: int, seat_price: int, overage_amount: int}
     */
    public function calculateOverage(string $tenantId, Plan $plan, Subscription $subscription): array;

    /**
     * Check if a tenant can add another seat.
     */
    public function canAddSeat(string $tenantId, Plan $plan): bool;

    /**
     * Get the maximum number of seats allowed for this pricing model.
     */
    public function getMaxSeats(Plan $plan): ?int;
}
