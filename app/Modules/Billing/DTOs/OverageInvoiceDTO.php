<?php

namespace App\Modules\Billing\DTOs;

class OverageInvoiceDTO
{
    public function __construct(
        public readonly string $tenantId,
        public readonly int $subscriptionId,
        public readonly int $totalSeats,
        public readonly int $includedSeats,
        public readonly int $overageSeats,
        public readonly int $seatPrice,
        public readonly int $overageAmount,
        public readonly string $currency,
        public readonly string $billingPeriodStart,
        public readonly string $billingPeriodEnd,
        public readonly array $metadata = [],
    ) {}

    public static function fromCalculation(array $calculation, array $plan, string $billingPeriodStart, string $billingPeriodEnd): self
    {
        return new self(
            tenantId: $calculation['tenant_id'],
            subscriptionId: $calculation['subscription_id'],
            totalSeats: $calculation['total_billable'],
            includedSeats: $plan['default_seats'] ?? 1,
            overageSeats: $calculation['overage'],
            seatPrice: $plan['seat_price'] ?? 0,
            overageAmount: $calculation['overage'] * ($plan['seat_price'] ?? 0),
            currency: $plan['currency'] ?? 'BDT',
            billingPeriodStart: $billingPeriodStart,
            billingPeriodEnd: $billingPeriodEnd,
        );
    }
}
