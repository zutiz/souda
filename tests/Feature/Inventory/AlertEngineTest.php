<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Inventory\Events\LowStockAlert;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\AlertEngine;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();

    tenancy()->initialize($this->user->tenant);

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
    $this->alertEngine = app(AlertEngine::class);

    // Seed initial stock
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-ALERT',
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->productB->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 50,
        movementType: 'initial_stock',
        reference: 'INIT-ALERT-B',
    );
});

test('findLowStock returns empty when stock is above threshold', function () {
    $result = $this->alertEngine->findLowStock();

    expect($result)->toHaveCount(0);
});

test('findLowStock returns items below threshold', function () {
    // Deduct to below threshold
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -95,
        movementType: 'sale_deduction',
        reference: 'SALE-001',
    );

    $result = $this->alertEngine->findLowStock();

    expect($result)->toHaveCount(1)
        ->and($result->first()->product_id)->toBe($this->product->id);
});

test('findLowStock filters by warehouse', function () {
    $otherWarehouse = Warehouse::factory()->create(['slug' => 'other-wh']);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $otherWarehouse->id,
        quantity: 3,
        movementType: 'initial_stock',
        reference: 'INIT-OTHER',
    );

    $all = $this->alertEngine->findLowStock();
    $filtered = $this->alertEngine->findLowStock($otherWarehouse->id);

    expect($filtered)->toHaveCount(1);
});

test('findDeadStock returns items with no recent movement', function () {
    $result = $this->alertEngine->findDeadStock(days: 1);

    // Stock was just created, so it should not be dead
    expect($result)->toHaveCount(0);
});

test('findOverstock returns items above threshold', function () {
    $result = $this->alertEngine->findOverstock(threshold: 49);

    expect($result)->toHaveCount(2);
});

test('findOverstock returns empty when no items exceed threshold', function () {
    $result = $this->alertEngine->findOverstock(threshold: 9999);

    expect($result)->toHaveCount(0);
});

test('evaluate dispatches low stock alert when below threshold', function () {
    Illuminate\Support\Facades\Event::fake();

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -95,
        movementType: 'sale_deduction',
        reference: 'SALE-002',
    );

    $this->alertEngine->evaluate(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    Event::assertDispatched(LowStockAlert::class);
});

test('getDashboardStats returns expected shape', function () {
    $stats = $this->alertEngine->getDashboardStats();

    expect($stats)->toHaveKeys([
        'total_stock_value', 'today_movements_in',
        'today_movements_out', 'low_stock_count', 'expiring_count',
    ]);
});
