<?php

declare(strict_types=1);

use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Enums\TransferStatusEnum;
use App\Modules\Inventory\Events\TransferCancelled;
use App\Modules\Inventory\Events\TransferCompleted;
use App\Modules\Inventory\Events\TransferInitiated;
use App\Modules\Inventory\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Exceptions\InvalidTransferStateException;
use App\Modules\Inventory\Exceptions\TransferNotFoundException;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryTransfer;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Inventory\Services\ReservationEngine;
use App\Modules\Inventory\Services\TransferEngine;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->sharedSubscribed()->create();

    tenancy()->initialize($this->user->tenant);
    app(TenantManager::class)->initialize($this->user->tenant);

    $this->product = Product::factory()->create();
    $this->productB = Product::factory()->create();

    $this->fromWarehouse = Warehouse::factory()->create(['slug' => 'from-wh']);
    $this->toWarehouse = Warehouse::factory()->create(['slug' => 'to-wh']);

    $this->inventoryEngine = app(InventoryEngine::class);
    $this->transferEngine = app(TransferEngine::class);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->fromWarehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-001',
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->productB->id,
        variantId: null,
        warehouseId: $this->fromWarehouse->id,
        quantity: 50,
        movementType: 'initial_stock',
        reference: 'INIT-002',
    );
});

test('can initiate a transfer in draft status', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 10],
        ],
    );

    expect($transfer)->toBeInstanceOf(InventoryTransfer::class)
        ->and($transfer->status)->toBe(TransferStatusEnum::Draft)
        ->and($transfer->reference)->not->toBeNull()
        ->and($transfer->items)->toHaveCount(1);
});

test('initiate creates reservations for transferred stock', function () {
    $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 30],
        ],
    );

    $available = app(ReservationEngine::class)->getAvailableQuantity(
        warehouseId: $this->fromWarehouse->id,
        productId: $this->product->id,
    );

    expect($available)->toBe(70);
});

test('initiate throws when source equals destination', function () {
    expect(fn () => $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->fromWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 5],
        ],
    ))->toThrow(InvalidArgumentException::class);
});

test('initiate throws on insufficient stock', function () {
    expect(fn () => $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 999],
        ],
    ))->toThrow(InsufficientStockException::class);
});

test('initiate dispatches TransferInitiated event', function () {
    Event::fake([TransferInitiated::class]);

    $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 10],
        ],
    );

    Event::assertDispatched(TransferInitiated::class);
});

test('can send a draft transfer', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 20],
        ],
    );

    $sent = $this->transferEngine->send($transfer->id);

    expect($sent->status)->toBe(TransferStatusEnum::InTransit)
        ->and($sent->sent_at)->not->toBeNull();
});

test('send records transfer_out movement on source warehouse', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 20],
        ],
    );

    $this->transferEngine->send($transfer->id);

    $balance = InventoryBalance::where([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->fromWarehouse->id,
    ])->first();

    expect($balance->quantity)->toBe(80);
});

test('send throws on non-draft transfer', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 10],
        ],
    );

    $this->transferEngine->send($transfer->id);

    expect(fn () => $this->transferEngine->send($transfer->id))
        ->toThrow(InvalidTransferStateException::class);
});

test('send throws on non-existent transfer', function () {
    expect(fn () => $this->transferEngine->send(99999))
        ->toThrow(TransferNotFoundException::class);
});

test('can receive an in-transit transfer', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 20],
        ],
    );

    $this->transferEngine->send($transfer->id);
    $received = $this->transferEngine->receive($transfer->id);

    expect($received->status)->toBe(TransferStatusEnum::Completed)
        ->and($received->received_at)->not->toBeNull();
});

test('receive records transfer_in movement on destination warehouse', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 20],
        ],
    );

    $this->transferEngine->send($transfer->id);
    $this->transferEngine->receive($transfer->id);

    $balance = InventoryBalance::where([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->toWarehouse->id,
    ])->first();

    expect($balance)->not->toBeNull()
        ->and($balance->quantity)->toBe(20);
});

test('receive dispatches TransferCompleted event', function () {
    Event::fake([TransferCompleted::class]);

    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 10],
        ],
    );

    $this->transferEngine->send($transfer->id);
    $this->transferEngine->receive($transfer->id);

    Event::assertDispatched(TransferCompleted::class);
});

test('can cancel a draft transfer', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 20],
        ],
    );

    $cancelled = $this->transferEngine->cancel($transfer->id);

    expect($cancelled->status)->toBe(TransferStatusEnum::Cancelled)
        ->and($cancelled->cancelled_at)->not->toBeNull();
});

test('cancel releases reservation on draft transfer', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 20],
        ],
    );

    $this->transferEngine->cancel($transfer->id);

    $balance = InventoryBalance::where([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->fromWarehouse->id,
    ])->first();

    expect($balance->reserved_quantity)->toBe(0);
});

test('cancel returns stock on in-transit transfer', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 20],
        ],
    );

    $this->transferEngine->send($transfer->id);

    $balanceAfterSend = InventoryBalance::where([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->fromWarehouse->id,
    ])->first();

    expect($balanceAfterSend->quantity)->toBe(80);

    $this->transferEngine->cancel($transfer->id);

    $balanceAfterCancel = InventoryBalance::where([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->fromWarehouse->id,
    ])->first();

    expect($balanceAfterCancel->quantity)->toBe(100);
});

test('cancel dispatches TransferCancelled event', function () {
    Event::fake([TransferCancelled::class]);

    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 10],
        ],
    );

    $this->transferEngine->cancel($transfer->id);

    Event::assertDispatched(TransferCancelled::class);
});

test('cant cancel completed transfer', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 10],
        ],
    );

    $this->transferEngine->send($transfer->id);
    $this->transferEngine->receive($transfer->id);

    expect(fn () => $this->transferEngine->cancel($transfer->id))
        ->toThrow(InvalidTransferStateException::class);
});

test('multi-product transfer', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 10],
            ['product_id' => $this->productB->id, 'variant_id' => null, 'quantity' => 20],
        ],
    );

    expect($transfer->items)->toHaveCount(2);

    $this->transferEngine->send($transfer->id);
    $this->transferEngine->receive($transfer->id);

    $balanceA = InventoryBalance::where([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->toWarehouse->id,
    ])->first();

    $balanceB = InventoryBalance::where([
        'product_id' => $this->productB->id,
        'warehouse_id' => $this->toWarehouse->id,
    ])->first();

    expect($balanceA->quantity)->toBe(10)
        ->and($balanceB->quantity)->toBe(20);
});

test('partial receive updates only specified items', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 10],
            ['product_id' => $this->productB->id, 'variant_id' => null, 'quantity' => 20],
        ],
    );

    $this->transferEngine->send($transfer->id);

    $itemA = $transfer->items()->where('product_id', $this->product->id)->first();
    $itemB = $transfer->items()->where('product_id', $this->productB->id)->first();

    $this->transferEngine->partialReceive($transfer->id, [
        $itemA->id => 5,
    ]);

    $itemA->refresh();
    $itemB->refresh();

    expect($itemA->quantity_received)->toBe(5)
        ->and($itemB->quantity_received)->toBe(0);

    $balanceA = InventoryBalance::where([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->toWarehouse->id,
    ])->first();

    expect($balanceA->quantity)->toBe(5);
});
