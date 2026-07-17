<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum ReservationStatusEnum: string
{
    case Active = 'active';
    case Consumed = 'consumed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Consumed => 'Consumed',
            self::Expired => 'Expired',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Consumed, self::Expired, self::Cancelled], true);
    }
}
