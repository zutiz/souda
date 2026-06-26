<?php

declare(strict_types=1);

namespace App\Modules\Product\Enums;

enum MediaTypeEnum: string
{
    case Image = 'image';
    case Video = 'video';
    case Document = 'document';

    public function label(): string
    {
        return match ($this) {
            self::Image => 'Image',
            self::Video => 'Video',
            self::Document => 'Document',
        };
    }

    public function isImage(): bool
    {
        return $this === self::Image;
    }
}
