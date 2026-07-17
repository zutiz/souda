<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum VelocityClassEnum: string
{
    case Fast = 'fast';
    case Slow = 'slow';
    case Dead = 'dead';
    case New = 'new';

    public function label(): string
    {
        return match ($this) {
            self::Fast => 'Fast Moving',
            self::Slow => 'Slow Moving',
            self::Dead => 'Dead Stock',
            self::New => 'New (Insufficient Data)',
        };
    }
}
