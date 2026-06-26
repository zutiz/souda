<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Contracts\StockChecker;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\WarehouseStock;
use Illuminate\Database\Eloquent\Collection;

class EloquentStockChecker implements StockChecker
{
    public function getAvailableQuantity(string $productId, ?string $variantId = null, ?int $warehouseId = null): int
    {
        $query = WarehouseStock::query()
            ->where('product_id', $productId)
            ->when($variantId, fn ($q) => $q->where('variant_id', $variantId))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId));

        return (int) $query->sum('available_quantity');
    }

    public function getTotalAvailable(string $productId, ?string $variantId = null): int
    {
        $product = Product::query()->find($productId);

        if ($product === null) {
            return 0;
        }

        if ($variantId !== null) {
            return $product->variants()
                ->where('id', $variantId)
                ->first()
                ?->warehouseStock()
                ->sum('available_quantity') ?? 0;
        }

        return $product->total_available;
    }

    public function isAvailable(string $productId, ?string $variantId = null, int $quantity = 1): bool
    {
        return $this->getTotalAvailable($productId, $variantId) >= $quantity;
    }

    public function getLowStockProducts(?int $threshold = null): Collection
    {
        $threshold ??= 5;

        $stockIds = WarehouseStock::query()
            ->whereRaw('(quantity - reserved_quantity) <= reorder_level')
            ->whereRaw('(quantity - reserved_quantity) > 0')
            ->where('reorder_level', '>=', $threshold)
            ->pluck('product_id')
            ->unique();

        return Product::query()->whereIn('id', $stockIds)->get();
    }

    public function getWarehouseStock(string $productId, ?string $variantId = null): Collection
    {
        return WarehouseStock::query()
            ->with('warehouse')
            ->where('product_id', $productId)
            ->when($variantId, fn ($q) => $q->where('variant_id', $variantId))
            ->get();
    }
}
