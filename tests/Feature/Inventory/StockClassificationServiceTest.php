<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Inventory\Services\StockClassificationService;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();

    tenancy()->initialize($this->user->tenant);

    $this->warehouse = Warehouse::factory()->create();
    $this->inventoryEngine = app(InventoryEngine::class);
    $this->classificationService = app(StockClassificationService::class);
});

test('classifyAbc assigns A to top 80% value items', function () {
    $productA = Product::factory()->create(['track_inventory' => true]);
    $productB = Product::factory()->create(['track_inventory' => true]);
    $productC = Product::factory()->create(['track_inventory' => true]);

    $this->inventoryEngine->recordMovement(
        productId: $productA->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-ABC-1',
        unitCost: 8000,
    );

    $this->inventoryEngine->recordMovement(
        productId: $productB->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-ABC-2',
        unitCost: 1500,
    );

    $this->inventoryEngine->recordMovement(
        productId: $productC->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-ABC-3',
        unitCost: 500,
    );

    $result = $this->classificationService->classifyAbc();

    expect($result)->toHaveKeys(['a', 'b', 'c']);

    $balanceA = InventoryBalance::where('product_id', $productA->id)->first();
    $balanceB = InventoryBalance::where('product_id', $productB->id)->first();
    $balanceC = InventoryBalance::where('product_id', $productC->id)->first();

    expect($balanceA->abc_class)->toBe('a');
    expect($balanceB->abc_class)->toBe('b');
    expect($balanceC->abc_class)->toBe('c');
});

test('classifyVelocity detects dead stock', function () {
    $product = Product::factory()->create(['track_inventory' => true]);

    $this->inventoryEngine->recordMovement(
        productId: $product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-VEL-1',
    );

    $balance = InventoryBalance::where('product_id', $product->id)->first();
    $balance->update(['last_movement_at' => now()->subDays(200)]);

    $result = $this->classificationService->classifyVelocity();

    expect($result['dead'])->toBeGreaterThanOrEqual(1);

    $balance->refresh();
    expect($balance->velocity_class)->toBe('dead');
});

test('classifyVelocity detects fast moving stock', function () {
    $product = Product::factory()->create(['track_inventory' => true]);

    $this->inventoryEngine->recordMovement(
        productId: $product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 500,
        movementType: 'initial_stock',
        reference: 'INIT-VEL-2',
    );

    // Simulate multiple sales to create velocity
    for ($i = 0; $i < 5; $i++) {
        $this->inventoryEngine->recordMovement(
            productId: $product->id,
            variantId: null,
            warehouseId: $this->warehouse->id,
            quantity: -20,
            movementType: 'sale_deduction',
            reference: "SALE-VEL-{$i}",
        );
    }

    $velocity = $this->classificationService->classifyVelocity();

    $balance = InventoryBalance::where('product_id', $product->id)->first();
    $balance->refresh();

    expect($velocity['fast'])->toBeGreaterThanOrEqual(1);
    expect($balance->velocity_class)->toBe('fast');
});

test('classifyVelocity detects slow moving stock', function () {
    $product = Product::factory()->create(['track_inventory' => true]);

    $this->inventoryEngine->recordMovement(
        productId: $product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-VEL-3',
    );

    $this->inventoryEngine->recordMovement(
        productId: $product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -1,
        movementType: 'sale_deduction',
        reference: 'SALE-VEL-SLOW',
    );

    $result = $this->classificationService->classifyVelocity();

    $balance = InventoryBalance::where('product_id', $product->id)->first();
    $balance->refresh();

    expect($balance->velocity_class)->toBe('slow');
});

test('getClassificationStats returns counts by class', function () {
    $product = Product::factory()->create(['track_inventory' => true]);

    $this->inventoryEngine->recordMovement(
        productId: $product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-STATS',
    );

    $this->classificationService->classifyAll();

    $stats = $this->classificationService->getClassificationStats();

    expect($stats)->toHaveKeys(['abc', 'velocity']);
    expect($stats['abc'])->toHaveKeys(['a', 'b', 'c']);
    expect($stats['velocity'])->toHaveKeys(['fast', 'slow', 'dead', 'new']);
});

test('classifyAll returns results for both analyses', function () {
    $product = Product::factory()->create(['track_inventory' => true]);

    $this->inventoryEngine->recordMovement(
        productId: $product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-ALL',
    );

    $result = $this->classificationService->classifyAll();

    expect($result)->toHaveKeys(['abc', 'velocity']);
    expect($result['abc'])->toHaveKeys(['a', 'b', 'c']);
    expect($result['velocity'])->toHaveKeys(['fast', 'slow', 'dead', 'new']);
});
