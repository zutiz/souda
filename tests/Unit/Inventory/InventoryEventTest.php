<?php

use App\Modules\Inventory\DTOs\InventoryMovementDTO;
use App\Modules\Inventory\Events\BatchDepleted;
use App\Modules\Inventory\Events\BatchExpiring;
use App\Modules\Inventory\Events\BatchQuarantined;
use App\Modules\Inventory\Events\InventoryAdjusted;
use App\Modules\Inventory\Events\InventoryDeducted;
use App\Modules\Inventory\Events\InventoryRestored;
use App\Modules\Inventory\Events\LowStockAlert;
use App\Modules\Inventory\Events\SerialNumberSold;
use App\Modules\Inventory\Events\StockDepleted;
use App\Modules\Inventory\Events\StockReservationCancelled;
use App\Modules\Inventory\Events\StockReservationCreated;
use App\Modules\Inventory\Events\StockReservationExpired;
use App\Modules\Inventory\Events\TransferCancelled;
use App\Modules\Inventory\Events\TransferCompleted;
use App\Modules\Inventory\Events\TransferInitiated;
use App\Modules\Shared\Contracts\DomainEvent;
use Carbon\CarbonImmutable;

function inventoryEventTestMovement(): InventoryMovementDTO
{
    return InventoryMovementDTO::fromArray([
        'product_id' => 'prod-1',
        'variant_id' => null,
        'warehouse_id' => 'wh-1',
        'quantity_change' => -5,
        'quantity_after' => 45,
        'type' => 'deduction',
        'reference_type' => 'order',
        'reference_id' => 'ord-123',
        'reason' => 'Order fulfillment',
        'occurred_at' => '2026-05-01T10:00:00Z',
    ]);
}

test('inventory deducted implements domain event', function () {
    $event = new InventoryDeducted(
        movement: inventoryEventTestMovement(),
        orderId: 'ord-123',
    );

    expect($event)->toBeInstanceOf(DomainEvent::class)
        ->and($event->getEventName())->toBe('inventory.deducted')
        ->and($event->orderId)->toBe('ord-123');
});

test('inventory deducted envelope contains movement data', function () {
    $event = new InventoryDeducted(
        movement: inventoryEventTestMovement(),
        orderId: 'ord-123',
    );

    $envelope = $event->toEnvelope();

    expect($envelope->payload['order_id'])->toBe('ord-123')
        ->and($envelope->payload['movement']['product_id'])->toBe('prod-1')
        ->and($envelope->payload['movement']['quantity_change'])->toBe(-5);
});

test('inventory restored implements domain event', function () {
    $event = new InventoryRestored(
        movement: inventoryEventTestMovement(),
        orderId: 'ord-123',
    );

    expect($event)->toBeInstanceOf(DomainEvent::class)
        ->and($event->getEventName())->toBe('inventory.restored');
});

test('inventory adjusted event has reason', function () {
    $movement = InventoryMovementDTO::fromArray([
        'product_id' => 'prod-1',
        'warehouse_id' => 'wh-1',
        'quantity_change' => 10,
        'quantity_after' => 60,
        'type' => 'adjustment',
        'reason' => 'Cycle count correction',
        'occurred_at' => '2026-05-01T12:00:00Z',
    ]);

    $event = new InventoryAdjusted(
        movement: $movement,
        reason: 'Cycle count correction',
    );

    expect($event->getEventName())->toBe('inventory.adjusted')
        ->and($event->reason)->toBe('Cycle count correction');
});

test('StockReservationCreated carries all fields', function () {
    $event = new StockReservationCreated(
        reservationId: 1,
        productId: 'prod-1',
        variantId: 'var-1',
        warehouseId: 10,
        quantity: 25,
    );

    expect($event->reservationId)->toBe(1)
        ->and($event->productId)->toBe('prod-1')
        ->and($event->variantId)->toBe('var-1')
        ->and($event->warehouseId)->toBe(10)
        ->and($event->quantity)->toBe(25)
        ->and($event->occurredAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('StockReservationCreated with null variant', function () {
    $event = new StockReservationCreated(
        reservationId: 2,
        productId: 'prod-2',
        variantId: null,
        warehouseId: 10,
        quantity: 5,
    );

    expect($event->variantId)->toBeNull();
});

test('StockReservationCancelled carries all fields', function () {
    $event = new StockReservationCancelled(
        reservationId: 1,
        productId: 'prod-1',
        variantId: null,
        warehouseId: 10,
    );

    expect($event->reservationId)->toBe(1)
        ->and($event->productId)->toBe('prod-1');
});

test('StockReservationExpired carries all fields', function () {
    $event = new StockReservationExpired(
        reservationId: 3,
        productId: 'prod-3',
        variantId: null,
        warehouseId: 10,
    );

    expect($event->reservationId)->toBe(3);
});

test('TransferInitiated carries all fields', function () {
    $event = new TransferInitiated(
        transferId: 100,
        fromWarehouseId: 1,
        toWarehouseId: 2,
        itemCount: 5,
    );

    expect($event->transferId)->toBe(100)
        ->and($event->fromWarehouseId)->toBe(1)
        ->and($event->toWarehouseId)->toBe(2)
        ->and($event->itemCount)->toBe(5);
});

test('TransferCompleted carries all fields', function () {
    $event = new TransferCompleted(
        transferId: 100,
        fromWarehouseId: 1,
        toWarehouseId: 2,
        itemCount: 5,
    );

    expect($event->receivedAt)->toBeInstanceOf(CarbonImmutable::class);
});

test('TransferCancelled with reason', function () {
    $event = new TransferCancelled(
        transferId: 100,
        reason: 'Insufficient stock at origin',
    );

    expect($event->reason)->toBe('Insufficient stock at origin');
});

test('TransferCancelled without reason', function () {
    $event = new TransferCancelled(
        transferId: 100,
    );

    expect($event->reason)->toBeNull();
});

test('StockDepleted carries product and warehouse', function () {
    $event = new StockDepleted(
        productId: 'prod-1',
        warehouseId: 'wh-1',
    );

    expect($event->productId)->toBe('prod-1')
        ->and($event->warehouseId)->toBe('wh-1')
        ->and($event->variantId)->toBeNull();
});

test('StockDepleted with variant', function () {
    $event = new StockDepleted(
        productId: 'prod-1',
        warehouseId: 'wh-1',
        variantId: 'var-1',
    );

    expect($event->variantId)->toBe('var-1');
});

test('LowStockAlert carries all fields', function () {
    $event = new LowStockAlert(
        productId: 'prod-1',
        warehouseId: 'wh-1',
        currentQuantity: 5,
        threshold: 10,
    );

    expect($event->currentQuantity)->toBe(5)
        ->and($event->threshold)->toBe(10);
});

test('BatchExpiring carries all fields', function () {
    $expiry = new CarbonImmutable('2026-08-01');
    $event = new BatchExpiring(
        batchId: 1,
        productId: 'prod-1',
        expiryDate: $expiry,
        daysRemaining: 14,
    );

    expect($event->batchId)->toBe(1)
        ->and($event->expiryDate)->toEqual($expiry)
        ->and($event->daysRemaining)->toBe(14);
});

test('BatchDepleted carries all fields', function () {
    $event = new BatchDepleted(
        batchId: 1,
        productId: 'prod-1',
        batchNumber: 'B-001',
    );

    expect($event->batchNumber)->toBe('B-001');
});

test('BatchQuarantined carries all fields', function () {
    $event = new BatchQuarantined(
        batchId: 1,
        productId: 'prod-1',
        batchNumber: 'B-001',
        reason: 'Quality check failed',
    );

    expect($event->reason)->toBe('Quality check failed');
});

test('BatchQuarantined without reason', function () {
    $event = new BatchQuarantined(
        batchId: 1,
        productId: 'prod-1',
        batchNumber: 'B-001',
    );

    expect($event->reason)->toBeNull();
});

test('SerialNumberSold carries all fields', function () {
    $event = new SerialNumberSold(
        serialId: 1,
        serialNumber: 'SN-10001',
        productId: 'prod-1',
        orderReference: 'ORD-001',
    );

    expect($event->serialNumber)->toBe('SN-10001')
        ->and($event->orderReference)->toBe('ORD-001');
});
