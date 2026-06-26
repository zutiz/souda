<?php

declare(strict_types=1);

namespace App\Modules\Product\Enums;

enum MovementTypeEnum: string
{
    case Received = 'received';
    case Sold = 'sold';
    case Return = 'return';
    case Adjustment = 'adjustment';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case Damaged = 'damaged';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Received => 'Received',
            self::Sold => 'Sold',
            self::Return => 'Return',
            self::Adjustment => 'Adjustment',
            self::TransferIn => 'Transfer In',
            self::TransferOut => 'Transfer Out',
            self::Damaged => 'Damaged',
            self::Expired => 'Expired',
        };
    }

    public function isInbound(): bool
    {
        return in_array($this, [self::Received, self::Return, self::TransferIn], true);
    }

    public function isOutbound(): bool
    {
        return in_array($this, [self::Sold, self::TransferOut, self::Damaged, self::Expired], true);
    }
}
