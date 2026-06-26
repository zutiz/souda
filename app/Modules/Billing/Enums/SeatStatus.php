<?php

namespace App\Modules\Billing\Enums;

enum SeatStatus: string
{
    case Active = 'active';
    case Pending = 'pending'; // invitation sent, not yet accepted
    case Released = 'released';

    public function isConsumed(): bool
    {
        return $this !== self::Released;
    }
}
