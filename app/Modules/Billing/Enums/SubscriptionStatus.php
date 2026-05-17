<?php

namespace App\Modules\Billing\Enums;

enum SubscriptionStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case Grace = 'grace';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case PendingPayment = 'pending_payment';

    /**
     * Determine if the subscription is considered active (accessible).
     */
    public function isAccessible(): bool
    {
        return match ($this) {
            self::Trial, self::Active, self::Grace => true,
            default => false,
        };
    }

    /**
     * Determine if the subscription requires billing action.
     */
    public function requiresPayment(): bool
    {
        return match ($this) {
            self::PendingPayment, self::Expired => true,
            default => false,
        };
    }
}
