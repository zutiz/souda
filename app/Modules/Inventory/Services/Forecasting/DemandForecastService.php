<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Forecasting;

use App\Modules\Inventory\Models\DemandForecast;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Services\Forecasting\Contracts\ForecastingStrategy;
use App\Modules\Inventory\Services\Forecasting\Strategies\LinearTrendStrategy;
use App\Modules\Inventory\Services\Forecasting\Strategies\MovingAverageStrategy;
use App\Modules\Inventory\Services\Forecasting\Strategies\SeasonalStrategy;

class DemandForecastService
{
    /** @var array<string, ForecastingStrategy> */
    protected array $strategies = [];

    public function __construct()
    {
        $this->registerStrategy(app(MovingAverageStrategy::class));
        $this->registerStrategy(app(SeasonalStrategy::class));
        $this->registerStrategy(app(LinearTrendStrategy::class));
    }

    public function registerStrategy(ForecastingStrategy $strategy): void
    {
        $this->strategies[$strategy->name()] = $strategy;
    }

    /** @return array<string, ForecastingStrategy> */
    public function getStrategies(): array
    {
        return $this->strategies;
    }

    public function getStrategy(string $name): ?ForecastingStrategy
    {
        return $this->strategies[$name] ?? null;
    }

    public function forecast(
        string $productId,
        int $warehouseId,
        ?string $variantId = null,
        ?string $strategyName = null,
        int $horizonDays = 30,
        array $config = [],
    ): DemandForecast {
        $strategy = $strategyName !== null
            ? $this->strategies[$strategyName]
            : $this->strategies['moving_average'];

        $config = array_merge($this->defaultConfig($strategy->name()), $config);

        $result = $strategy->predict(
            productId: $productId,
            warehouseId: $warehouseId,
            variantId: $variantId,
            horizonDays: $horizonDays,
            config: $config,
        );

        $periodStart = now()->addDay();
        $periodEnd = now()->addDays($horizonDays);

        return DemandForecast::create([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'forecast_date' => $periodStart,
            'forecast_quantity' => $result->forecastQuantity,
            'confidence_lower' => $result->confidenceLower,
            'confidence_upper' => $result->confidenceUpper,
            'model_used' => $result->modelUsed,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'metadata' => $result->metadata,
        ]);
    }

    public function forecastAll(
        ?string $strategyName = null,
        ?int $warehouseId = null,
        int $horizonDays = 30,
    ): int {
        $balances = InventoryBalance::where('quantity', '>', 0)
            ->select('product_id', 'warehouse_id')
            ->distinct()
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->get();

        $count = 0;

        foreach ($balances as $balance) {
            $this->forecast(
                productId: $balance->product_id,
                warehouseId: $balance->warehouse_id,
                strategyName: $strategyName,
                horizonDays: $horizonDays,
            );

            $count++;
        }

        return $count;
    }

    public function forecastAllStrategies(
        ?int $warehouseId = null,
        int $horizonDays = 30,
    ): array {
        $results = [];

        foreach (array_keys($this->strategies) as $name) {
            $results[$name] = $this->forecastAll(
                strategyName: $name,
                warehouseId: $warehouseId,
                horizonDays: $horizonDays,
            );
        }

        return $results;
    }

    public function resolveExpiredForecasts(?int $daysOld = null): int
    {
        $cutoff = now()->subDays($daysOld ?? 1);

        $expiredForecasts = DemandForecast::whereNull('actual_quantity')
            ->where('period_end', '<', $cutoff)
            ->get();

        $count = 0;

        foreach ($expiredForecasts as $forecast) {
            $actualQuantity = (int) InventoryBalance::where('product_id', $forecast->product_id)
                ->where('warehouse_id', $forecast->warehouse_id)
                ->value('quantity');

            $forecast->recordActual($actualQuantity);
            $count++;
        }

        return $count;
    }

    public function getForecastHistory(
        string $productId,
        int $warehouseId,
        int $limit = 12,
    ) {
        return DemandForecast::byProduct($productId, $warehouseId)
            ->latest('forecast_date')
            ->limit($limit)
            ->get();
    }

    public function getUpcomingForecasts(
        ?int $warehouseId = null,
        int $days = 30,
    ) {
        return DemandForecast::with(['product:id,name,sku'])
            ->upcoming($days)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->latest()
            ->paginate(25);
    }

    private function defaultConfig(string $strategyName): array
    {
        return match ($strategyName) {
            'moving_average' => [
                'window_days' => (int) config('inventory.sales_velocity_days', 30),
            ],
            'seasonal' => [
                'period_months' => (int) config('inventory.forecast_seasonal_period_months', 12),
            ],
            'linear_trend' => [
                'lookback_days' => (int) config('inventory.forecast_trend_lookback_days', 90),
            ],
            default => [],
        };
    }
}
