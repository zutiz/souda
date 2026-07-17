<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Console\Commands;

use App\Modules\Inventory\Services\Forecasting\DemandForecastService;
use Illuminate\Console\Command;

class GenerateForecastsCommand extends Command
{
    protected $signature = 'inventory:generate-forecasts
        {--strategy= : Specific strategy to use (moving_average, seasonal, linear_trend)}
        {--warehouse= : Warehouse ID to scope forecast generation}
        {--horizon=30 : Forecast horizon in days}
        {--all-strategies : Run all strategies and compare}
        {--resolve-expired : Resolve expired forecasts with actual quantities}';

    protected $description = 'Generate demand forecasts for all active inventory balances';

    public function handle(DemandForecastService $forecastService): int
    {
        $warehouseId = $this->option('warehouse') ? (int) $this->option('warehouse') : null;
        $horizon = (int) $this->option('horizon');

        if ($this->option('resolve-expired')) {
            $resolved = $forecastService->resolveExpiredForecasts();
            $this->info("Resolved {$resolved} expired forecast(s).");

            if ($this->option('no-interaction')) {
                return 0;
            }
        }

        if ($this->option('all-strategies')) {
            $results = $forecastService->forecastAllStrategies(
                warehouseId: $warehouseId,
                horizonDays: $horizon,
            );

            $this->table(
                ['Strategy', 'Forecasts Generated'],
                collect($results)->map(fn ($count, $name) => [$name, $count])->toArray(),
            );

            $total = array_sum($results);
            $this->info("Generated {$total} forecast(s) across ".count($results).' strategy(ies).');

            return 0;
        }

        $strategy = $this->option('strategy') ? (string) $this->option('strategy') : null;

        $count = $forecastService->forecastAll(
            strategyName: $strategy,
            warehouseId: $warehouseId,
            horizonDays: $horizon,
        );

        $strategyLabel = $strategy ?? 'default (moving_average)';
        $this->info("Generated {$count} forecast(s) using '{$strategyLabel}' strategy.");

        return 0;
    }
}
