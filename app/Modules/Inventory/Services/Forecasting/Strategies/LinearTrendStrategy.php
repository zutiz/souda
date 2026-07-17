<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Forecasting\Strategies;

use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Services\Forecasting\Contracts\ForecastingStrategy;
use App\Modules\Inventory\Services\Forecasting\Contracts\ForecastResult;
use Illuminate\Support\Facades\DB;

class LinearTrendStrategy implements ForecastingStrategy
{
    public function name(): string
    {
        return 'linear_trend';
    }

    public function predict(
        string $productId,
        int $warehouseId,
        ?string $variantId,
        int $horizonDays,
        array $config,
    ): ForecastResult {
        $lookbackDays = (int) ($config['lookback_days'] ?? 90);
        $cutoff = now()->subDays($lookbackDays);

        $query = InventoryLedger::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '<', 0)
            ->where('created_at', '>=', $cutoff);

        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        }

        $dailyData = (clone $query)
            ->selectRaw('DATE(created_at) as day, SUM(quantity) as total_out')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('day')
            ->get();

        if ($dailyData->isEmpty()) {
            return new ForecastResult(
                forecastQuantity: 0,
                confidenceLower: 0,
                confidenceUpper: 0,
                modelUsed: $this->name(),
                metadata: ['lookback_days' => $lookbackDays, 'data_points' => 0],
            );
        }

        $n = $dailyData->count();
        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($dailyData as $i => $row) {
            $x = $i + 1;
            $y = abs((int) $row->total_out);
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $denominator = $n * $sumX2 - $sumX * $sumX;

        if ($denominator === 0 || $n === 0) {
            $totalQty = array_sum($dailyData->pluck('total_out')->map(fn ($v) => abs((int) $v))->toArray());

            return new ForecastResult(
                forecastQuantity: max(0, (int) round(($totalQty / max($n, 1)) * $horizonDays)),
                confidenceLower: 0,
                confidenceUpper: 0,
                modelUsed: $this->name(),
                metadata: ['lookback_days' => $lookbackDays, 'data_points' => $n, 'fallback' => 'flat_line'],
            );
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;
        $intercept = ($sumY - $slope * $sumX) / $n;

        $predictedDaily = $intercept + $slope * ($n + $horizonDays / 2);
        $forecastQuantity = (int) round(max(0, $predictedDaily * $horizonDays));

        $stdError = $this->computeStandardError($dailyData, $slope, $intercept, $n);
        $margin = (int) round($stdError * $horizonDays * 1.96);

        return new ForecastResult(
            forecastQuantity: $forecastQuantity,
            confidenceLower: max(0, $forecastQuantity - $margin),
            confidenceUpper: $forecastQuantity + $margin,
            modelUsed: $this->name(),
            metadata: [
                'lookback_days' => $lookbackDays,
                'data_points' => $n,
                'slope' => round($slope, 4),
                'intercept' => round($intercept, 2),
                'std_error' => round($stdError, 2),
            ],
        );
    }

    private function computeStandardError($data, float $slope, float $intercept, int $n): float
    {
        if ($n < 3) {
            return 0;
        }

        $sumSquaredError = 0;

        foreach ($data as $i => $row) {
            $x = $i + 1;
            $y = abs((int) $row->total_out);
            $predicted = $intercept + $slope * $x;
            $sumSquaredError += ($y - $predicted) ** 2;
        }

        return sqrt($sumSquaredError / ($n - 2));
    }
}
