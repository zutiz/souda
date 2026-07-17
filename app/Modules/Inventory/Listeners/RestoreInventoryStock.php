<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Listeners;

use App\Modules\Inventory\DTOs\InventoryMovementDTO;
use App\Modules\Inventory\Events\InventoryRestored;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Order\Events\OrderCancelled;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;

class RestoreInventoryStock implements ShouldQueue
{
    public string $queue = 'critical';

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        protected InventoryEngine $inventoryEngine,
    ) {}

    public function handle(OrderCancelled $event): void
    {
        foreach ($event->order->lineItems as $item) {
            $ledger = $this->inventoryEngine->recordMovement(
                productId: $item->productId,
                variantId: $item->variantId,
                warehouseId: (int) ($item->warehouseId ?? '1'),
                quantity: $item->quantity,
                movementType: 'reversal',
                reference: "cancel:{$event->order->orderNumber}",
                description: "Restored from cancelled order #{$event->order->orderNumber}",
            );

            $movement = new InventoryMovementDTO(
                productId: $item->productId,
                variantId: $item->variantId,
                warehouseId: $item->warehouseId ?? '1',
                quantityChange: $item->quantity,
                quantityAfter: $ledger->quantity_after,
                type: 'reversal',
                referenceType: 'order_cancelled',
                referenceId: $event->order->orderId,
                reason: $event->reason,
                metadata: ['order_number' => $event->order->orderNumber],
                occurredAt: new CarbonImmutable,
            );

            (new InventoryRestored(
                movement: $movement,
                orderId: $event->order->orderId,
                correlationId: $event->getCorrelationId(),
            ))->dispatch();
        }
    }

    public function failed(OrderCancelled $event, \Throwable $e): void
    {
        logger()->error('RestoreInventoryStock failed', [
            'order_id' => $event->order->orderId,
            'error' => $e->getMessage(),
        ]);
    }
}
