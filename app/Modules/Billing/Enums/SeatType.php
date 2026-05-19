<?php

namespace App\Modules\Billing\Enums;

enum SeatType: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Staff = 'staff';

    public function consumesSeat(): bool
    {
        return true;
    }

    public static function billableTypes(): array
    {
        return [self::Owner, self::Admin, self::Staff];
    }

    public static function nonBillableTypes(): array
    {
        return []; // API and system are not stored as SeatType
    }
}
