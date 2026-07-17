<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum CountStatusEnum: string
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Verified = 'verified';
    case Adjusted = 'adjusted';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::InProgress => 'In Progress',
            self::Verified => 'Verified',
            self::Adjusted => 'Adjusted',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::Draft || $this === self::InProgress;
    }

    public function isAdjustable(): bool
    {
        return $this === self::Verified;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled], true);
    }
}
