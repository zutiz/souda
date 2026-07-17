<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Forecasting\Strategies;

use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Services\Forecasting\Contracts\ForecastingStrategy;
use App\Modules\Inventory\Services\Forecasting\Contracts\ForecastResult;

class SeasonalStrategy implements ForecastingStrategy
{
    public function name(): string
    {
        return 'seasonal';
    }

    public function predict(
        string $productId,
        int $warehouseId,
        ?string $variantId,
        int $horizonDays,
        array $config,
    ): ForecastResult {
        $periodMonths = (int) ($config['period_months'] ?? 12);
        $periodStart = now()->subMonths($periodMonths);

        $query = InventoryLedger::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '<', 0)
            ->where('created_at', '>=', $periodStart);

        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        }

        $monthlyData = (clone $query)
            ->selectRaw('MONTH(created_at) as month_num, SUM(quantity) as total_out')
            ->groupBy('month_num')
            ->pluck('total_out', 'month_num')
            ->toArray();

        if (empty($monthlyData)) {
            return new ForecastResult(
                forecastQuantity: 0,
                confidenceLower: 0,
                confidenceUpper: 0,
                modelUsed: $this->name(),
                metadata: ['period_months' => $periodMonths, 'months_with_data' => 0],
            );
        }

        $currentMonth = (int) now()->month;
        $totalDays = 0;
        $totalOut = 0;

        foreach ($monthlyData as $month => $out) {
            $totalOut += abs((int) $out);
            $totalDays += $this->daysInMonth((int) $month);
        }

        $dailyAverage = $totalDays > 0 ? $totalOut / $totalDays : 0;
        $forecastQuantity = (int) round($dailyAverage * $horizonDays);
        $margin = (int) round($forecastQuantity * 0.3);

        return new ForecastResult(
            forecastQuantity: max(0, $forecastQuantity),
            confidenceLower: max(0, $forecastQuantity - $margin),
            confidenceUpper: $forecastQuantity + $margin,
            modelUsed: $this->name(),
            metadata: [
                'period_months' => $periodMonths,
                'months_with_data' => count($monthlyData),
                'monthly_breakdown' => $monthlyData,
            ],
        );
    }

    private function daysInMonth(int $month): int
    {
        return (int) now()->month($month)->daysInMonth;
    }
}
