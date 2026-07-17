<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Listeners;

use App\Modules\Inventory\DTOs\InventoryMovementDTO;
use App\Modules\Inventory\Events\InventoryDeducted;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Order\Events\OrderCreated;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeductInventoryStock implements ShouldQueue
{
    public string $queue = 'critical';

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        protected InventoryEngine $inventoryEngine,
    ) {}

    public function handle(OrderCreated $event): void
    {
        foreach ($event->order->lineItems as $item) {
            $ledger = $this->inventoryEngine->recordMovement(
                productId: $item->productId,
                variantId: $item->variantId,
                warehouseId: (int) ($item->warehouseId ?? '1'),
                quantity: -$item->quantity,
                movementType: 'sale_deduction',
                reference: "order:{$event->order->orderNumber}",
            );

            $movement = new InventoryMovementDTO(
                productId: $item->productId,
                variantId: $item->variantId,
                warehouseId: $item->warehouseId ?? '1',
                quantityChange: -$item->quantity,
                quantityAfter: $ledger->quantity_after,
                type: 'sale_deduction',
                referenceType: 'order',
                referenceId: $event->order->orderId,
                reason: "Order #{$event->order->orderNumber} deduction",
                metadata: ['order_number' => $event->order->orderNumber],
                occurredAt: new CarbonImmutable,
            );

            (new InventoryDeducted(
                movement: $movement,
                orderId: $event->order->orderId,
                correlationId: $event->getCorrelationId(),
            ))->dispatch();
        }
    }

    public function failed(OrderCreated $event, \Throwable $e): void
    {
        logger()->error('DeductInventoryStock failed', [
            'order_id' => $event->order->orderId,
            'error' => $e->getMessage(),
        ]);
    }
}
