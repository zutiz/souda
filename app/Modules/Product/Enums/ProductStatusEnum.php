<?php

declare(strict_types=1);

namespace App\Modules\Product\Enums;

enum ProductStatusEnum: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Archived => 'Archived',
        };
    }

    public function isAccessible(): bool
    {
        return $this === self::Active;
    }

    public function isSearchable(): bool
    {
        return $this === self::Active;
    }
}
