<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum SerialStatusEnum: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Sold = 'sold';
    case Returned = 'returned';
    case Quarantined = 'quarantined';
    case Disposed = 'disposed';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::Reserved => 'Reserved',
            self::Sold => 'Sold',
            self::Returned => 'Returned',
            self::Quarantined => 'Quarantined',
            self::Disposed => 'Disposed',
        };
    }

    public function isAvailable(): bool
    {
        return $this === self::Available;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Sold, self::Disposed], true);
    }
}
