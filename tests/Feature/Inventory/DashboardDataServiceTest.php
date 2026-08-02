<?php

declare(strict_types=1);

use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Models\DemandForecast;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\DashboardDataService;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Inventory\Services\StockClassificationService;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->sharedSubscribed()->create();

    tenancy()->initialize($this->user->tenant);
    app(TenantManager::class)->initialize($this->user->tenant);

    $this->product = Product::factory()->create([
        'low_stock_threshold' => 10,
        'track_inventory' => true,
    ]);

    $this->productB = Product::factory()->create([
        'low_stock_threshold' => 5,
        'track_inventory' => true,
    ]);

    $this->warehouse = Warehouse::factory()->create();

    $this->inventoryEngine = app(InventoryEngine::class);
    $this->dashboardService = app(DashboardDataService::class);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 200,
        movementType: 'initial_stock',
        reference: 'INIT-DASH',
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->productB->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-DASH-B',
    );

    for ($i = 0; $i < 5; $i++) {
        $this->inventoryEngine->recordMovement(
            productId: $this->product->id,
            variantId: null,
            warehouseId: $this->warehouse->id,
            quantity: -5,
            movementType: 'sale_deduction',
            reference: "SALE-DASH-{$i}",
        );
    }
});

test('getMovementTrend returns daily movement data', function () {
    $trend = $this->dashboardService->getMovementTrend(days: 30);

    expect($trend)->toBeCollection()
        ->and($trend->count())->toBeGreaterThanOrEqual(1);

    $first = $trend->first();
    expect($first)->toHaveKeys(['date', 'quantity_in', 'quantity_out', 'net_movement'])
        ->and($first['quantity_in'])->toBeGreaterThan(0);
});

test('getStockValueTrend returns value data', function () {
    $trend = $this->dashboardService->getStockValueTrend(days: 30);

    expect($trend)->toBeCollection();

    if ($trend->isNotEmpty()) {
        expect($trend->first())->toHaveKeys(['date', 'value']);
    }
});

test('getTopMovingProducts returns top products by movement', function () {
    $top = $this->dashboardService->getTopMovingProducts(limit: 5, days: 30);

    expect($top)->toBeCollection()
        ->and($top->count())->toBeGreaterThanOrEqual(1);

    $first = $top->first();
    expect($first)->toHaveKeys(['product_id', 'product_name', 'sku', 'total_out', 'movement_days'])
        ->and($first['total_out'])->toBeGreaterThan(0);
});

test('getClassificationDistribution returns abc and velocity stats', function () {
    $classifyService = app(StockClassificationService::class);
    $classifyService->classifyAll();

    $dist = $this->dashboardService->getClassificationDistribution();

    expect($dist)->toHaveKeys(['abc', 'velocity'])
        ->and($dist['abc'])->toHaveKeys(['a', 'b', 'c'])
        ->and($dist['velocity'])->toHaveKeys(['fast', 'slow', 'dead', 'new']);
});

test('getHealthScore returns score structure', function () {
    $score = $this->dashboardService->getHealthScore();

    expect($score)->toHaveKeys(['score', 'grade', 'low_stock_ratio', 'dead_stock_ratio', 'avg_velocity'])
        ->and($score['score'])->toBeBetween(0, 100)
        ->and($score['grade'])->toBeIn(['healthy', 'fair', 'critical']);
});

test('getForecastAccuracy returns empty collection when no forecasts', function () {
    $accuracy = $this->dashboardService->getForecastAccuracy();

    expect($accuracy)->toBeCollection()
        ->and($accuracy)->toHaveCount(0);
});

test('getForecastAccuracy returns data when forecasts exist with accuracy', function () {
    DemandForecast::factory()->accurate()->create([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
        'model_used' => 'moving_average',
    ]);

    $accuracy = $this->dashboardService->getForecastAccuracy();

    expect($accuracy)->toHaveCount(1);

    $first = $accuracy->first();
    expect($first)->toHaveKeys(['model_used', 'avg_accuracy', 'count'])
        ->and($first['avg_accuracy'])->toBeGreaterThan(0);
});

test('getDashboardData returns all chart data keys', function () {
    $data = $this->dashboardService->getDashboardData(days: 30);

    expect($data)->toHaveKeys([
        'movement_trend',
        'stock_value_trend',
        'top_moving_products',
        'classification_distribution',
        'dead_stock_trend',
        'health_score',
        'forecast_accuracy',
    ]);
});

test('getDeadStockTrend returns dead stock records', function () {
    $trend = $this->dashboardService->getDeadStockTrend(days: 30);

    expect($trend)->toBeCollection();

    if ($trend->isNotEmpty()) {
        expect($trend->first())->toHaveKeys(['date', 'dead_stock_count', 'dead_stock_value']);
    }
});
