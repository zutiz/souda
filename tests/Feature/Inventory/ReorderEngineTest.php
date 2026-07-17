<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Inventory\Models\PurchaseSuggestion;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Inventory\Services\ReorderEngine;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();

    tenancy()->initialize($this->user->tenant);

    $this->product = Product::factory()->create([
        'low_stock_threshold' => 10,
        'lead_time_days' => 5,
        'safety_stock' => 3,
        'track_inventory' => true,
    ]);

    $this->productB = Product::factory()->create([
        'low_stock_threshold' => 20,
        'lead_time_days' => null,
        'safety_stock' => null,
        'track_inventory' => true,
    ]);

    $this->warehouse = Warehouse::factory()->create();

    $this->inventoryEngine = app(InventoryEngine::class);
    $this->reorderEngine = app(ReorderEngine::class);

    // Seed stock
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-REORDER',
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->productB->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 50,
        movementType: 'initial_stock',
        reference: 'INIT-REORDER-B',
    );
});

test('calculateSalesVelocity returns zero when no sales', function () {
    $velocity = $this->reorderEngine->calculateSalesVelocity(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        days: 30,
    );

    expect($velocity)->toBe(0.0);
});

test('calculateSalesVelocity computes daily average', function () {
    // Record sales over multiple days
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -10,
        movementType: 'sale_deduction',
        reference: 'SALE-001',
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -20,
        movementType: 'sale_deduction',
        reference: 'SALE-002',
    );

    $velocity = $this->reorderEngine->calculateSalesVelocity(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        days: 30,
    );

    expect($velocity)->toBeGreaterThan(0);
});

test('calculateReorderQuantity returns correct amount', function () {
    $suggested = $this->reorderEngine->calculateReorderQuantity(
        currentQuantity: 5,
        reservedQuantity: 1,
        reorderLevel: 10,
        leadTimeDays: 5,
        safetyStock: 3,
        salesVelocity: 2.0,
    );

    // Lead time demand = 2 * 5 = 10
    // Target = 10 + 10 + 3 = 23
    // Available = 5 - 1 = 4
    // Suggested = 23 - 4 = 19
    expect($suggested)->toBe(19);
});

test('calculateReorderQuantity returns zero when stock is adequate', function () {
    $suggested = $this->reorderEngine->calculateReorderQuantity(
        currentQuantity: 100,
        reservedQuantity: 0,
        reorderLevel: 10,
        leadTimeDays: 5,
        safetyStock: 3,
        salesVelocity: 2.0,
    );

    expect($suggested)->toBe(0);
});

test('generateSuggestions creates suggestions for low stock products', function () {
    // Deduct product A to below threshold
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -95,
        movementType: 'sale_deduction',
        reference: 'SALE-BELOW',
    );

    $count = $this->reorderEngine->generateSuggestions();

    expect($count)->toBe(1);

    $suggestion = PurchaseSuggestion::first();

    expect($suggestion)
        ->product_id->toBe($this->product->id)
        ->warehouse_id->toBe($this->warehouse->id)
        ->status->toBe('pending')
        ->suggested_quantity->toBeGreaterThan(0);
});

test('generateSuggestions updates existing pending suggestion', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -95,
        movementType: 'sale_deduction',
        reference: 'SALE-BELOW',
    );

    $this->reorderEngine->generateSuggestions();

    $first = PurchaseSuggestion::first();
    $originalQty = $first->suggested_quantity;

    // Run again — should update instead of duplicate
    $count = $this->reorderEngine->generateSuggestions();

    expect($count)->toBe(1);
    expect(PurchaseSuggestion::count())->toBe(1);
});

test('generateSuggestions respects warehouse filter', function () {
    $otherWarehouse = Warehouse::factory()->create(['slug' => 'other-wh']);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $otherWarehouse->id,
        quantity: 5,
        movementType: 'initial_stock',
        reference: 'INIT-OTHER',
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -95,
        movementType: 'sale_deduction',
        reference: 'SALE-LOW',
    );

    $count = $this->reorderEngine->generateSuggestions($this->warehouse->id);

    expect($count)->toBe(1);
});

test('dismiss marks suggestion as dismissed', function () {
    $suggestion = PurchaseSuggestion::factory()->create();

    $this->reorderEngine->dismiss($suggestion, 'Not needed');

    expect($suggestion->fresh()->status)->toBe('dismissed');
});

test('markOrdered marks suggestion as ordered', function () {
    $suggestion = PurchaseSuggestion::factory()->create();

    $this->reorderEngine->markOrdered($suggestion, 'PO-001');

    expect($suggestion->fresh()->status)->toBe('ordered');
});

test('uses default lead_time and safety_stock from config when product has none', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->productB->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -45,
        movementType: 'sale_deduction',
        reference: 'SALE-BELOW-B',
    );

    $count = $this->reorderEngine->generateSuggestions();

    expect($count)->toBeGreaterThanOrEqual(1);
});
