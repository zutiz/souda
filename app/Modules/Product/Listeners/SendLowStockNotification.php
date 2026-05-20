<?php

declare(strict_types=1);

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\LowStockAlert;

class SendLowStockNotification
{
    public function handle(LowStockAlert $event): void
    {
        logger()->warning('Low stock alert', [
            'product_id' => $event->productId,
            'variant_id' => $event->variantId,
            'warehouse_id' => $event->warehouseId,
            'available' => $event->availableQuantity,
            'threshold' => $event->threshold,
        ]);
    }
}
