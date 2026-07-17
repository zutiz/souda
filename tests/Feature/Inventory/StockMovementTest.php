<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Inventory\DTOs\StockMovementDTO;
use App\Modules\Inventory\Enums\MovementTypeEnum;
use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Services\StockMovementEngine;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Warehouse;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();

    tenancy()->initialize($this->user->tenant);

    $this->product = Product::factory()->create();
    $this->warehouse = Warehouse::factory()->create();
});

test('can record an inbound stock movement', function () {
    $engine = app(StockMovementEngine::class);

    $ledger = $engine->record(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        type: MovementTypeEnum::PurchaseReceipt,
        reference: 'PO-001',
        unitCost: 5000,
    );

    expect($ledger)->toBeInstanceOf(InventoryLedger::class)
        ->and($ledger->product_id)->toBe($this->product->id)
        ->and($ledger->warehouse_id)->toBe($this->warehouse->id)
        ->and($ledger->quantity)->toBe(100)
        ->and($ledger->movement_type)->toBe(MovementTypeEnum::PurchaseReceipt)
        ->and($ledger->unit_cost)->toBe(5000)
        ->and($ledger->reference)->toBe('PO-001');
});

test('can record an outbound stock movement', function () {
    $engine = app(StockMovementEngine::class);

    $engine->record(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        type: MovementTypeEnum::PurchaseReceipt,
        reference: 'PO-001',
    );

    $ledger = $engine->record(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -25,
        type: MovementTypeEnum::SaleDeduction,
        reference: 'SALE-001',
    );

    expect($ledger->quantity)->toBe(-25)
        ->and($ledger->movement_type)->toBe(MovementTypeEnum::SaleDeduction);
});

test('generates unique reference numbers', function () {
    $engine = app(StockMovementEngine::class);

    $firstRef = $engine->generateReference(MovementTypeEnum::PurchaseReceipt);

    $engine->record(
        $this->product->id, null, $this->warehouse->id, 10,
        MovementTypeEnum::PurchaseReceipt, $firstRef,
    );

    $secondRef = $engine->generateReference(MovementTypeEnum::PurchaseReceipt);

    expect($firstRef)->not->toBe($secondRef)
        ->and($firstRef)->toMatch('/^PUR-\d{8}-\d{4}$/');
});

test('can find movement by id', function () {
    $engine = app(StockMovementEngine::class);

    $ledger = $engine->record(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 50,
        type: MovementTypeEnum::InitialStock,
        reference: 'INIT-001',
    );

    $found = $engine->findById($ledger->id);

    expect($found)->not->toBeNull()
        ->and($found->id)->toBe($ledger->id);
});

test('can find movement by reference', function () {
    $engine = app(StockMovementEngine::class);

    $ledger = $engine->record(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 30,
        type: MovementTypeEnum::InitialStock,
        reference: 'INIT-FIND-001',
    );

    $found = $engine->findByReference('INIT-FIND-001');

    expect($found)->not->toBeEmpty()
        ->and($found->first()->reference)->toBe('INIT-FIND-001');
});

test('can filter movements by product and warehouse', function () {
    $engine = app(StockMovementEngine::class);

    $warehouse2 = Warehouse::factory()->create();
    $product2 = Product::factory()->create();

    $engine->record($this->product->id, null, $this->warehouse->id, 10, MovementTypeEnum::InitialStock, 'A1');
    $engine->record($this->product->id, null, $this->warehouse->id, 20, MovementTypeEnum::PurchaseReceipt, 'A2');
    $engine->record($this->product->id, null, $warehouse2->id, 30, MovementTypeEnum::InitialStock, 'A3');
    $engine->record($product2->id, null, $this->warehouse->id, 40, MovementTypeEnum::InitialStock, 'A4');

    $products = $engine->findByProduct(
        productId: $this->product->id,
        warehouseId: null,
    );
    expect($products)->toHaveCount(3);

    $warehouseMovements = $engine->findByProduct(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
    );
    expect($warehouseMovements)->toHaveCount(2);

    $results = $engine->findByProduct(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        limit: 1,
    );
    expect($results)->toHaveCount(1);
});

test('can record movement from DTO', function () {
    $engine = app(StockMovementEngine::class);

    $dto = new StockMovementDTO(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 75,
        type: MovementTypeEnum::InitialStock,
        reference: 'DTO-001',
        referenceType: 'initial_stock',
        unitCost: 1000,
    );

    $ledger = $engine->recordFromDTO($dto);

    expect($ledger->quantity)->toBe(75)
        ->and($ledger->reference)->toBe('DTO-001');
});

test('cannot find non-existent movement', function () {
    $engine = app(StockMovementEngine::class);

    expect($engine->findById(999999))->toBeNull()
        ->and($engine->findByReference('NONEXISTENT'))->isEmpty();
});
