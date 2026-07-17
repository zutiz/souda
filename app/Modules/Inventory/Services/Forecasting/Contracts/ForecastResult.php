<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Forecasting\Contracts;

class ForecastResult
{
    public function __construct(
        public readonly int $forecastQuantity,
        public readonly ?int $confidenceLower = null,
        public readonly ?int $confidenceUpper = null,
        public readonly string $modelUsed = 'moving_average',
        public readonly array $metadata = [],
    ) {}
}
