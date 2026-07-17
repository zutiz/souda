<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Forecasting\Contracts;

interface ForecastingStrategy
{
    public function name(): string;

    public function predict(
        string $productId,
        int $warehouseId,
        ?string $variantId,
        int $horizonDays,
        array $config,
    ): ForecastResult;
}
