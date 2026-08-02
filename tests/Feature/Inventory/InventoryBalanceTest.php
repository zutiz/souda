<?php

declare(strict_types=1);

use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Enums\MovementTypeEnum;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Services\InventoryBalanceService;
use App\Modules\Inventory\Services\StockMovementEngine;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Warehouse;

beforeEach(function () {
    $this->user = User::factory()->sharedSubscribed()->create();

    tenancy()->initialize($this->user->tenant);
    app(TenantManager::class)->initialize($this->user->tenant);

    $this->product = Product::factory()->create();
    $this->warehouse = Warehouse::factory()->create();
    $this->engine = app(StockMovementEngine::class);
    $this->balanceService = app(InventoryBalanceService::class);
});

test('recalculate creates balance record after first movement', function () {
    $this->engine->record(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        type: MovementTypeEnum::InitialStock,
        reference: 'INIT-001',
    );

    $balance = $this->balanceService->recalculate(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    expect($balance)->toBeInstanceOf(InventoryBalance::class)
        ->and($balance->product_id)->toBe($this->product->id)
        ->and($balance->warehouse_id)->toBe($this->warehouse->id)
        ->and($balance->quantity)->toBe(100);
});

test('recalculate correctly sums inbound and outbound movements', function () {
    $this->engine->record($this->product->id, null, $this->warehouse->id, 100, MovementTypeEnum::InitialStock, 'INIT-001');
    $this->engine->record($this->product->id, null, $this->warehouse->id, 100, MovementTypeEnum::PurchaseReceipt, 'PO-001');
    $this->engine->record($this->product->id, null, $this->warehouse->id, -30, MovementTypeEnum::SaleDeduction, 'SALE-001');

    $balance = $this->balanceService->recalculate(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    expect($balance->quantity)->toBe(170);
});

test('recalculate with variant isolation', function () {
    $variantA = '01JX4XC0Z1V3N0B4H7E2Y8T5M9';
    $variantB = '01JX4XC0Z2V3N0B4H7E2Y8T5M0';

    $this->engine->record($this->product->id, $variantA, $this->warehouse->id, 50, MovementTypeEnum::InitialStock, 'INIT-A');
    $this->engine->record($this->product->id, $variantB, $this->warehouse->id, 30, MovementTypeEnum::InitialStock, 'INIT-B');

    $balanceA = $this->balanceService->recalculate(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        variantId: $variantA,
    );

    $balanceB = $this->balanceService->recalculate(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        variantId: $variantB,
    );

    expect($balanceA->quantity)->toBe(50)
        ->and($balanceB->quantity)->toBe(30);
});

test('recalculate returns zero for product with no movements', function () {
    InventoryLedger::query()->where('product_id', $this->product->id)->delete();

    $balance = $this->balanceService->recalculate(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    expect($balance)->toBeInstanceOf(InventoryBalance::class)
        ->and($balance->quantity)->toBe(0);
});

test('getByProductAndWarehouse returns null for non-existent balance', function () {
    $balance = $this->balanceService->getByProductAndWarehouse(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    expect($balance)->toBeNull();
});

test('getByProductAndWarehouse returns existing balance', function () {
    $this->engine->record($this->product->id, null, $this->warehouse->id, 75, MovementTypeEnum::InitialStock, 'INIT-001');

    $this->balanceService->recalculate(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    $balance = $this->balanceService->getByProductAndWarehouse(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    expect($balance)->not->toBeNull()
        ->and($balance->quantity)->toBe(75);
});

test('rebuildFromLedger replays all movements and returns correct balance', function () {
    $this->engine->record($this->product->id, null, $this->warehouse->id, 200, MovementTypeEnum::InitialStock, 'INIT-001');
    $this->engine->record($this->product->id, null, $this->warehouse->id, -50, MovementTypeEnum::SaleDeduction, 'SALE-001');
    $this->engine->record($this->product->id, null, $this->warehouse->id, -20, MovementTypeEnum::SaleDeduction, 'SALE-002');

    $this->balanceService->rebuildFromLedger(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    $balance = $this->balanceService->getByProductAndWarehouse(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );

    expect($balance)->toBeInstanceOf(InventoryBalance::class)
        ->and($balance->quantity)->toBe(130);
});

test('multiple recalculations idempotent - same quantity', function () {
    $this->engine->record($this->product->id, null, $this->warehouse->id, 50, MovementTypeEnum::InitialStock, 'INIT-001');

    $first = $this->balanceService->recalculate($this->product->id, $this->warehouse->id);
    $second = $this->balanceService->recalculate($this->product->id, $this->warehouse->id);

    expect($second->quantity)->toBe($first->quantity)->toBe(50);
});

test('balance has default lock version', function () {
    $this->engine->record($this->product->id, null, $this->warehouse->id, 100, MovementTypeEnum::InitialStock, 'INIT-001');

    $balance = $this->balanceService->recalculate($this->product->id, $this->warehouse->id);

    expect($balance->lockVersion())->toBe(0);
});
