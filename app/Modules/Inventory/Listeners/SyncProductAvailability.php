<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Listeners;

use App\Modules\Inventory\Events\StockMovementCreated;
use App\Modules\Product\Models\Product;

class SyncProductAvailability
{
    public function handle(StockMovementCreated $event): void
    {
        $ledger = $event->ledger;

        $product = Product::find($ledger->product_id);

        if ($product === null) {
            return;
        }

        $product->updateTimestamps();
    }
}
