<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Models\WarehouseStock;
use Illuminate\Database\UniqueConstraintViolationException;

class StockLockService
{
    public function lockStockRecord(int $warehouseId, ?string $productId, ?string $variantId): WarehouseStock
    {
        $stock = WarehouseStock::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->lockForUpdate()
            ->first();

        if ($stock === null) {
            try {
                $stock = WarehouseStock::query()->create([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'reorder_level' => 5,
                ]);
            } catch (UniqueConstraintViolationException) {
                $stock = WarehouseStock::query()
                    ->where('warehouse_id', $warehouseId)
                    ->where('product_id', $productId)
                    ->where('variant_id', $variantId)
                    ->lockForUpdate()
                    ->firstOrFail();
            }
        }

        return $stock;
    }

    public function lockStockRecords(array $warehouseIds, ?string $productId, ?string $variantId): array
    {
        $uniqueIds = array_values(array_unique($warehouseIds));

        sort($uniqueIds);

        $records = [];

        foreach ($uniqueIds as $warehouseId) {
            $records[$warehouseId] = $this->lockStockRecord($warehouseId, $productId, $variantId);
        }

        return $records;
    }
}
