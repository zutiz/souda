<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

enum ForecastModelEnum: string
{
    case MovingAverage = 'moving_average';
    case Seasonal = 'seasonal';
    case LinearTrend = 'linear_trend';

    public function label(): string
    {
        return match ($this) {
            self::MovingAverage => 'Moving Average',
            self::Seasonal => 'Seasonal (YoY)',
            self::LinearTrend => 'Linear Trend',
        };
    }
}
