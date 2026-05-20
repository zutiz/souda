<?php

use App\Modules\Inventory\DTOs\InventoryMovementDTO;
use App\Modules\Inventory\Events\InventoryAdjusted;
use App\Modules\Inventory\Events\InventoryDeducted;
use App\Modules\Inventory\Events\InventoryRestored;
use App\Modules\Shared\Contracts\DomainEvent;

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
