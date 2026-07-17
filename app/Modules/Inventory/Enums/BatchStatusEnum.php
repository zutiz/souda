<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum BatchStatusEnum: string
{
    case Active = 'active';
    case Depleted = 'depleted';
    case Quarantined = 'quarantined';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Depleted => 'Depleted',
            self::Quarantined => 'Quarantined',
            self::Expired => 'Expired',
        };
    }

    public function isAvailable(): bool
    {
        return $this === self::Active;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Depleted, self::Expired], true);
    }
}
