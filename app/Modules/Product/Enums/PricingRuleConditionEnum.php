<?php

declare(strict_types=1);

namespace App\Modules\Product\Enums;

enum PricingRuleConditionEnum: string
{
    case Quantity = 'quantity';
    case CartTotal = 'cart_total';
    case CustomerGroup = 'customer_group';
    case DateRange = 'date_range';

    public function label(): string
    {
        return match ($this) {
            self::Quantity => 'Quantity',
            self::CartTotal => 'Cart Total',
            self::CustomerGroup => 'Customer Group',
            self::DateRange => 'Date Range',
        };
    }
}
