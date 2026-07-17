<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum TransferStatusEnum: string
{
    case Draft = 'draft';
    case InTransit = 'in_transit';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InTransit => 'In Transit',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }
}
