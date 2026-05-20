<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\Contracts\StockAllocator;
use App\Modules\Product\DTOs\AllocationResult;
use App\Modules\Product\Exceptions\InsufficientStockException;
use App\Modules\Product\Models\WarehouseStock;

class DefaultStockAllocator implements StockAllocator
{
    public function __construct(
        protected StockService $stockService,
    ) {}

    public function allocate(array $lineItems): AllocationResult
    {
        $allocations = [];
        $failedItems = [];

        foreach ($lineItems as $item) {
            $productId = $item['product_id'] ?? null;
            $variantId = $item['variant_id'] ?? null;
            $quantity = (int) ($item['quantity'] ?? 1);

            $bestWarehouse = $this->findBestWarehouse($productId, $variantId, $quantity);

            if ($bestWarehouse === null) {
                $failedItems[] = $item;

                continue;
            }

            $allocations[] = [
                'warehouse_id' => $bestWarehouse->warehouse_id,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
            ];
        }

        if (! empty($failedItems)) {
            return AllocationResult::failed(
                allocations: $allocations,
                failedItems: $failedItems,
                error: new InsufficientStockException('One or more items could not be allocated'),
            );
        }

        return AllocationResult::success($allocations);
    }

    protected function findBestWarehouse(?string $productId, ?string $variantId, int $quantity): ?WarehouseStock
    {
        return WarehouseStock::query()
            ->where('product_id', $productId)
            ->when($variantId, fn ($q) => $q->where('variant_id', $variantId))
            ->whereRaw('(quantity - reserved_quantity) >= ?', [$quantity])
            ->orderBy('quantity', 'desc')
            ->first();
    }
}
