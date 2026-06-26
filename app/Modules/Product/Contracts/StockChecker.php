<?php

declare(strict_types=1);

namespace App\Modules\Product\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface StockChecker
{
    public function getAvailableQuantity(string $productId, ?string $variantId = null, ?int $warehouseId = null): int;

    public function getTotalAvailable(string $productId, ?string $variantId = null): int;

    public function isAvailable(string $productId, ?string $variantId = null, int $quantity = 1): bool;

    public function getLowStockProducts(?int $threshold = null): Collection;

    public function getWarehouseStock(string $productId, ?string $variantId = null): Collection;
}
