<?php

declare(strict_types=1);

namespace App\Modules\Product\Enums;

enum PricingRuleTypeEnum: string
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';
    case Tiered = 'tiered';

    public function label(): string
    {
        return match ($this) {
            self::Fixed => 'Fixed Amount',
            self::Percentage => 'Percentage',
            self::Tiered => 'Tiered',
        };
    }
}
