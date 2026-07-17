<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum CostingMethodEnum: string
{
    case WeightedAverage = 'weighted_average';
    case Fifo = 'fifo';
    case Lifo = 'lifo';

    public function label(): string
    {
        return match ($this) {
            self::WeightedAverage => 'Weighted Average',
            self::Fifo => 'FIFO',
            self::Lifo => 'LIFO',
        };
    }
}
