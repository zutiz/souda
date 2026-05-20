<?php

declare(strict_types=1);

namespace App\Modules\Product\Listeners;

use App\Modules\Product\Enums\ProductStatusEnum;
use App\Modules\Product\Events\StockDepleted;
use App\Modules\Product\Models\Product;

class MarkProductUnavailable
{
    public function handle(StockDepleted $event): void
    {
        $product = Product::query()->find($event->productId);

        if ($product !== null && $product->track_inventory) {
            $totalAvailable = $product->total_available;

            if ($totalAvailable <= 0) {
                $product->update(['status' => ProductStatusEnum::Archived]);
            }
        }
    }
}
