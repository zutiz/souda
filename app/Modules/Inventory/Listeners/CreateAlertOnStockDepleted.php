<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Listeners;

use App\Modules\Inventory\Enums\AlertSeverityEnum;
use App\Modules\Inventory\Events\StockDepleted;
use App\Modules\Inventory\Models\InventoryAlert;
use App\Modules\Product\Models\Product;

class CreateAlertOnStockDepleted
{
    public function handle(StockDepleted $event): void
    {
        $product = Product::find($event->productId);

        $exists = InventoryAlert::active()
            ->where('type', 'stock_depleted')
            ->where('product_id', $event->productId)
            ->where('warehouse_id', $event->warehouseId)
            ->exists();

        if ($exists) {
            return;
        }

        InventoryAlert::create([
            'type' => 'stock_depleted',
            'title' => 'Stock Depleted',
            'message' => sprintf(
                'Product "%s" is out of stock in warehouse #%s.',
                $product?->name ?? $event->productId,
                $event->warehouseId,
            ),
            'severity' => AlertSeverityEnum::Critical->value,
            'product_id' => $event->productId,
            'warehouse_id' => $event->warehouseId,
        ]);
    }
}
