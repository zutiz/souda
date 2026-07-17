<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum AbcClassEnum: string
{
    case A = 'a';
    case B = 'b';
    case C = 'c';

    public function label(): string
    {
        return match ($this) {
            self::A => 'A (High Value)',
            self::B => 'B (Medium Value)',
            self::C => 'C (Low Value)',
        };
    }
}
