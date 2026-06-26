<?php

declare(strict_types=1);

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Events\StockUpdated;

class UpdateProductStockCache
{
    public function handle(StockUpdated $event): void
    {
        $cacheKey = "product_stock_{$event->movement->productId}";

        cache()->forget($cacheKey);
    }
}
