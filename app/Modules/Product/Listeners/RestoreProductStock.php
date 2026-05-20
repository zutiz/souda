<?php

declare(strict_types=1);

namespace App\Modules\Product\Listeners;

use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Product\Services\StockService;
use App\Modules\Shared\Traits\HasIdempotency;
use Illuminate\Contracts\Queue\ShouldQueue;

class RestoreProductStock implements ShouldQueue
{
    use HasIdempotency;

    public string $queue = 'critical';

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 120, 300];

    public function __construct(
        protected StockService $stockService,
    ) {}

    public function handle(OrderCancelled $event): void
    {
        if ($this->alreadyProcessed($event)) {
            return;
        }

        foreach ($event->order->lineItems as $item) {
            $this->stockService->receiveStock(
                warehouseId: $item->warehouseId ?? '1',
                productId: $item->productId,
                variantId: $item->variantId,
                quantity: $item->quantity,
                notes: "Restored from cancelled order #{$event->order->orderNumber}",
            );
        }

        $this->markProcessed($event);
    }

    public function failed(OrderCancelled $event, \Throwable $e): void
    {
        $this->releaseIdempotency($event);

        $this->logFailure($event, $e, [
            'order_id' => $event->order->orderId,
        ]);
    }
}
