<?php

declare(strict_types=1);

namespace App\Modules\Product\Observers;

use App\Modules\Product\Models\WarehouseStock;

class WarehouseStockObserver
{
    public function updated(WarehouseStock $stock): void
    {
        $available = $stock->quantity - $stock->reserved_quantity;

        $product = $stock->product;

        if ($product !== null) {
            $product->refreshStockTotals();
        }
    }
}
