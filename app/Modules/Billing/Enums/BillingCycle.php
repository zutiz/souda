<?php

namespace App\Modules\Billing\Enums;

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';
    case Quarterly = 'quarterly';
    case Weekly = 'weekly';
    case Daily = 'daily';
    case Custom = 'custom';

    /**
     * Return the number of days for this billing cycle.
     */
    public function days(): int
    {
        return match ($this) {
            self::Daily => 1,
            self::Weekly => 7,
            self::Monthly => 30,
            self::Quarterly => 90,
            self::Yearly => 365,
            self::Custom => 0,
        };
    }
}
