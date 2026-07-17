<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Listeners;

use App\Modules\Inventory\Events\InventoryBalanceUpdated;
use App\Modules\Inventory\Services\ReorderEngine;
use Illuminate\Contracts\Queue\ShouldQueue;

class AutoReorderOnBalanceChange implements ShouldQueue
{
    public string $queue = 'low';

    public int $tries = 3;

    public function __construct(
        protected ReorderEngine $reorderEngine,
    ) {}

    public function handle(InventoryBalanceUpdated $event): void
    {
        $balance = $event->balance;

        $product = $balance->product;

        if ($product === null || ! $product->track_inventory) {
            return;
        }

        $threshold = (int) $product->low_stock_threshold;

        if ($balance->quantity <= $threshold && $event->previousQuantity > $threshold) {
            $this->reorderEngine->generateSuggestions($balance->warehouse_id);
        }
    }
}
