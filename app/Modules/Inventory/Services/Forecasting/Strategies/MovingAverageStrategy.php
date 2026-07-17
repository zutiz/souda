<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Forecasting\Strategies;

use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Services\Forecasting\Contracts\ForecastingStrategy;
use App\Modules\Inventory\Services\Forecasting\Contracts\ForecastResult;

class MovingAverageStrategy implements ForecastingStrategy
{
    public function name(): string
    {
        return 'moving_average';
    }

    public function predict(
        string $productId,
        int $warehouseId,
        ?string $variantId,
        int $horizonDays,
        array $config,
    ): ForecastResult {
        $windowDays = (int) ($config['window_days'] ?? 30);

        $query = InventoryLedger::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '<', 0)
            ->where('created_at', '>=', now()->subDays($windowDays));

        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        }

        $totalOut = (int) $query->sum('quantity');

        $movementDays = (int) (clone $query)
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as days')
            ->value('days');

        if ($movementDays === 0) {
            return new ForecastResult(
                forecastQuantity: 0,
                confidenceLower: 0,
                confidenceUpper: 0,
                modelUsed: $this->name(),
                metadata: ['window_days' => $windowDays, 'movement_days' => 0],
            );
        }

        $dailyAverage = abs($totalOut) / $movementDays;
        $forecastQuantity = (int) round($dailyAverage * $horizonDays);
        $margin = (int) round($forecastQuantity * 0.25);

        return new ForecastResult(
            forecastQuantity: max(0, $forecastQuantity),
            confidenceLower: max(0, $forecastQuantity - $margin),
            confidenceUpper: $forecastQuantity + $margin,
            modelUsed: $this->name(),
            metadata: [
                'window_days' => $windowDays,
                'movement_days' => $movementDays,
                'daily_average' => round($dailyAverage, 2),
            ],
        );
    }
}
