<?php

declare(strict_types=1);

namespace App\Modules\Product\Enums;

enum AttributeTypeEnum: string
{
    case Select = 'select';
    case MultiSelect = 'multi_select';
    case Text = 'text';
    case Textarea = 'textarea';
    case Color = 'color';
    case Swatch = 'swatch';

    public function label(): string
    {
        return match ($this) {
            self::Select => 'Select',
            self::MultiSelect => 'Multi Select',
            self::Text => 'Text',
            self::Textarea => 'Textarea',
            self::Color => 'Color',
            self::Swatch => 'Swatch',
        };
    }

    public function hasOptions(): bool
    {
        return in_array($this, [self::Select, self::MultiSelect, self::Color, self::Swatch], true);
    }
}
