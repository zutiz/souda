<?php

namespace App\Modules\Billing\Enums;

enum PricingModel: string
{
    case Flat = 'flat';
    case PerSeat = 'per_seat';
    case Tiered = 'tiered';
    case UsageBased = 'usage_based';

    public function isSeatBased(): bool
    {
        return $this === self::PerSeat || $this === self::Tiered;
    }

    public function requiresUsageTracking(): bool
    {
        return $this === self::UsageBased;
    }
}
