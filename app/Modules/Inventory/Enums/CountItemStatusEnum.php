<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum CountItemStatusEnum: string
{
    case Pending = 'pending';
    case Counted = 'counted';
    case Verified = 'verified';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Counted => 'Counted',
            self::Verified => 'Verified',
        };
    }
}
