<?php

declare(strict_types=1);

use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Events\InventoryBalanceUpdated;
use App\Modules\Inventory\Events\StockMovementCreated;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Warehouse;

beforeEach(function () {
    $this->user = User::factory()->sharedSubscribed()->create();

    tenancy()->initialize($this->user->tenant);
    app(TenantManager::class)->initialize($this->user->tenant);

    $this->product = Product::factory()->create();
    $this->warehouse = Warehouse::factory()->create();
    $this->inventoryEngine = app(InventoryEngine::class);
});

test('recordMovement creates ledger entry and updates balance', function () {
    $ledger = $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-001',
        unitCost: 5000,
    );

    expect($ledger)->toBeInstanceOf(InventoryLedger::class)
        ->and($ledger->quantity)->toBe(100);

    $balance = InventoryBalance::query()
        ->where('product_id', $this->product->id)
        ->where('warehouse_id', $this->warehouse->id)
        ->first();

    expect($balance)->not->toBeNull()
        ->and($balance->quantity)->toBe(100);
});

test('recordMovement throws exception for invalid movement type', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'invalid_type',
        reference: 'TEST',
    );
})->throws(InvalidArgumentException::class, 'Invalid movement type: invalid_type');

test('getBalance returns current stock level', function () {
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 75,
        movementType: 'initial_stock',
        reference: 'INIT-001',
    );

    $balance = $this->inventoryEngine->getBalance(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    expect($balance)->toBe(75);
});

test('getBalance returns zero for product with no stock', function () {
    $balance = $this->inventoryEngine->getBalance(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    expect($balance)->toBe(0);
});

test('recordMovement dispatches StockMovementCreated event', function () {
    Event::fake([StockMovementCreated::class, InventoryBalanceUpdated::class]);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 50,
        movementType: 'initial_stock',
        reference: 'INIT-EVENT-001',
    );

    Event::assertDispatched(StockMovementCreated::class);
    Event::assertDispatched(InventoryBalanceUpdated::class);
});
