<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Controllers;

use App\Modules\Inventory\Enums\ForecastModelEnum;
use App\Modules\Inventory\Models\DemandForecast;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\Forecasting\DemandForecastService;
use App\Modules\Inventory\Services\ReorderEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ForecastController
{
    public function __construct(
        protected DemandForecastService $forecastService,
        protected ReorderEngine $reorderEngine,
    ) {}

    public function index(Request $request): Response
    {
        $forecasts = $this->forecastService->getUpcomingForecasts(
            warehouseId: $request->input('warehouse_id') ? (int) $request->input('warehouse_id') : null,
            days: (int) ($request->input('days', 30)),
        );

        $warehouses = Warehouse::active()->get(['id', 'name']);

        $strategies = collect(ForecastModelEnum::cases())->map(fn ($s) => [
            'value' => $s->value,
            'label' => $s->label(),
        ]);

        return Inertia::render('Inventory/Forecasts/Index', [
            'forecasts' => $forecasts,
            'warehouses' => $warehouses,
            'strategies' => $strategies,
            'filters' => [
                'warehouse_id' => $request->input('warehouse_id'),
                'days' => $request->input('days', 30),
            ],
        ]);
    }

    public function show(DemandForecast $forecast): Response
    {
        $forecast->load(['product:id,name,sku']);

        $history = $this->forecastService->getForecastHistory(
            productId: $forecast->product_id,
            warehouseId: $forecast->warehouse_id,
            limit: 12,
        );

        $demandHistory = $this->reorderEngine->getDemandHistory(
            productId: $forecast->product_id,
            warehouseId: $forecast->warehouse_id,
            months: 12,
        );

        return Inertia::render('Inventory/Forecasts/Show', [
            'forecast' => $forecast,
            'history' => $history,
            'demandHistory' => $demandHistory,
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $strategy = $request->input('strategy');
        $warehouseId = $request->input('warehouse_id') ? (int) $request->input('warehouse_id') : null;
        $horizon = (int) ($request->input('horizon', 30));

        if ($strategy === 'all') {
            $results = $this->forecastService->forecastAllStrategies(
                warehouseId: $warehouseId,
                horizonDays: $horizon,
            );

            $total = array_sum($results);

            return redirect()->route('inventory.forecasts.index')
                ->with('success', "Generated {$total} forecast(s) across ".count($results).' strategies.');
        }

        $count = $this->forecastService->forecastAll(
            strategyName: $strategy ?: null,
            warehouseId: $warehouseId,
            horizonDays: $horizon,
        );

        return redirect()->route('inventory.forecasts.index')
            ->with('success', "Generated {$count} forecast(s).");
    }

    public function resolve(Request $request): RedirectResponse
    {
        $daysOld = (int) ($request->input('days_old', 1));
        $count = $this->forecastService->resolveExpiredForecasts($daysOld);

        return redirect()->route('inventory.forecasts.index')
            ->with('success', "Resolved {$count} expired forecast(s).");
    }
}
