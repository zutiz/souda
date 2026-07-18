<?php

declare(strict_types=1);

namespace App\Modules\Order\Enums;

enum FulfillmentStatusEnum: string
{
    case Unfulfilled = 'unfulfilled';
    case PartiallyFulfilled = 'partially_fulfilled';
    case Fulfilled = 'fulfilled';

    public function label(): string
    {
        return match ($this) {
            self::Unfulfilled => 'Unfulfilled',
            self::PartiallyFulfilled => 'Partially Fulfilled',
            self::Fulfilled => 'Fulfilled',
        };
    }
}
