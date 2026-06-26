<?php

declare(strict_types=1);

namespace App\Modules\Product\Enums;

enum ProductTypeEnum: string
{
    case Simple = 'simple';
    case Configurable = 'configurable';
    case Bundle = 'bundle';
    case Virtual = 'virtual';

    public function label(): string
    {
        return match ($this) {
            self::Simple => 'Simple',
            self::Configurable => 'Configurable',
            self::Bundle => 'Bundle',
            self::Virtual => 'Virtual',
        };
    }

    public function hasVariants(): bool
    {
        return $this === self::Configurable;
    }

    public function tracksInventory(): bool
    {
        return $this !== self::Virtual;
    }
}
