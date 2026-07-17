<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Inventory\Models\DemandForecast;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\Forecasting\DemandForecastService;
use App\Modules\Inventory\Services\Forecasting\Strategies\LinearTrendStrategy;
use App\Modules\Inventory\Services\Forecasting\Strategies\MovingAverageStrategy;
use App\Modules\Inventory\Services\Forecasting\Strategies\SeasonalStrategy;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();

    tenancy()->initialize($this->user->tenant);

    $this->product = Product::factory()->create(['track_inventory' => true]);
    $this->warehouse = Warehouse::factory()->create();
    $this->inventoryEngine = app(InventoryEngine::class);
    $this->forecastService = app(DemandForecastService::class);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 500,
        movementType: 'initial_stock',
        reference: 'INIT-FCST',
    );

    for ($i = 0; $i < 10; $i++) {
        $this->inventoryEngine->recordMovement(
            productId: $this->product->id,
            variantId: null,
            warehouseId: $this->warehouse->id,
            quantity: -10,
            movementType: 'sale_deduction',
            reference: "SALE-FCST-{$i}",
        );
    }
});

test('moving average strategy produces forecast', function () {
    $strategy = app(MovingAverageStrategy::class);

    $result = $strategy->predict(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        variantId: null,
        horizonDays: 30,
        config: ['window_days' => 30],
    );

    expect($result->forecastQuantity)->toBeGreaterThan(0);
    expect($result->modelUsed)->toBe('moving_average');
    expect($result->confidenceLower)->toBeLessThanOrEqual($result->forecastQuantity);
    expect($result->confidenceUpper)->toBeGreaterThanOrEqual($result->forecastQuantity);
});

test('seasonal strategy produces forecast', function () {
    $strategy = app(SeasonalStrategy::class);

    $result = $strategy->predict(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        variantId: null,
        horizonDays: 30,
        config: ['period_months' => 12],
    );

    expect($result->forecastQuantity)->toBeGreaterThan(0);
    expect($result->modelUsed)->toBe('seasonal');
});

test('linear trend strategy produces forecast', function () {
    $strategy = app(LinearTrendStrategy::class);

    $result = $strategy->predict(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        variantId: null,
        horizonDays: 30,
        config: ['lookback_days' => 90],
    );

    expect($result->forecastQuantity)->toBeGreaterThan(0);
    expect($result->modelUsed)->toBe('linear_trend');
});

test('forecast service creates demand forecast record', function () {
    $forecast = $this->forecastService->forecast(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    expect($forecast)->toBeInstanceOf(DemandForecast::class);
    expect($forecast->forecast_quantity)->toBeGreaterThan(0);
    expect($forecast->model_used)->toBe('moving_average');
    expect($forecast->product_id)->toBe($this->product->id);
});

test('forecast service with specific strategy', function () {
    $forecast = $this->forecastService->forecast(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        strategyName: 'linear_trend',
        horizonDays: 60,
    );

    expect($forecast->model_used)->toBe('linear_trend');
    expect($forecast->period_end->format('Y-m-d'))->toBe(now()->addDays(60)->format('Y-m-d'));
});

test('forecastAll generates forecasts for all balances', function () {
    $count = $this->forecastService->forecastAll(horizonDays: 30);

    expect($count)->toBeGreaterThanOrEqual(1);

    $saved = DemandForecast::where('product_id', $this->product->id)->count();
    expect($saved)->toBeGreaterThanOrEqual(1);
});

test('forecastAllStrategies runs all strategies', function () {
    $results = $this->forecastService->forecastAllStrategies(horizonDays: 30);

    expect($results)->toHaveKeys(['moving_average', 'seasonal', 'linear_trend']);
    foreach ($results as $name => $count) {
        expect($count)->toBeGreaterThanOrEqual(1);
    }
});

test('resolveExpiredForecasts updates expired forecasts', function () {
    $forecast = $this->forecastService->forecast(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    $forecast->update(['period_end' => now()->subDays(2)]);

    $count = $this->forecastService->resolveExpiredForecasts(daysOld: 1);

    expect($count)->toBeGreaterThanOrEqual(1);

    $forecast->refresh();
    expect($forecast->actual_quantity)->not->toBeNull();
    expect($forecast->accuracy_score)->not->toBeNull();
});

test('getUpcomingForecasts returns recent forecasts', function () {
    $this->forecastService->forecast(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    $upcoming = $this->forecastService->getUpcomingForecasts(days: 30);

    expect($upcoming->total())->toBeGreaterThanOrEqual(1);
});

test('demand forecast accuracy calculation', function () {
    $forecast = DemandForecast::factory()->accurate()->create();

    expect($forecast->computeAccuracy(100))->toBe(100.0);
    expect($forecast->computeAccuracy(80))->toBe(80.0);
    expect($forecast->computeAccuracy(120))->toBe(80.0);
});

test('strategies are registered', function () {
    $strategies = $this->forecastService->getStrategies();

    expect($strategies)->toHaveKeys(['moving_average', 'seasonal', 'linear_trend']);
    expect($this->forecastService->getStrategy('moving_average'))->not->toBeNull();
    expect($this->forecastService->getStrategy('unknown'))->toBeNull();
});
