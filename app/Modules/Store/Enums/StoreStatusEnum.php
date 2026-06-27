<?php

declare(strict_types=1);

namespace App\Modules\Store\Enums;

enum StoreStatusEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Paused = 'paused';
    case Provisioning = 'provisioning';

    public function isAccessible(): bool
    {
        return match ($this) {
            self::Active => true,
            default => false,
        };
    }
}
