<?php

declare(strict_types=1);

namespace App\Modules\Product\Listeners;

use App\Modules\Order\Events\OrderCreated;
use App\Modules\Product\Exceptions\InsufficientStockException;
use App\Modules\Product\Exceptions\OrderBackorderedException;
use App\Modules\Product\Services\StockService;
use App\Modules\Shared\Traits\HasIdempotency;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeductProductStock implements ShouldQueue
{
    use HasIdempotency;

    public string $queue = 'critical';

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        protected StockService $stockService,
        protected Dispatcher $events,
    ) {}

    public function handle(OrderCreated $event): void
    {
        if ($this->alreadyProcessed($event)) {
            return;
        }

        $lineItems = array_map(
            fn ($item) => [
                'warehouse_id' => (int) ($item->warehouseId ?? 1),
                'product_id' => $item->productId,
                'variant_id' => $item->variantId,
                'quantity' => $item->quantity,
                'order_id' => (int) $event->order->orderId,
            ],
            $event->order->lineItems,
        );

        try {
            $this->stockService->allocateForOrder($lineItems);

            $this->markProcessed($event);
        } catch (InsufficientStockException $e) {
            $this->events->dispatch(new OrderBackorderedException($event->order->orderId));

            throw $e;
        }
    }

    public function failed(OrderCreated $event, \Throwable $e): void
    {
        $this->releaseIdempotency($event);

        $this->logFailure($event, $e, [
            'order_id' => $event->order->orderId,
        ]);
    }
}
