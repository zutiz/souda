<?php

declare(strict_types=1);

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\StockDepleted;

class SendStockDepletedNotification
{
    public function handle(StockDepleted $event): void
    {
        logger()->warning('Stock depleted', [
            'product_id' => $event->productId,
            'variant_id' => $event->variantId,
            'warehouse_id' => $event->warehouseId,
        ]);
    }
}
