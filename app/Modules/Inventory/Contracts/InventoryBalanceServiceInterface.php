<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Contracts;

use App\Modules\Inventory\Models\InventoryBalance;

interface InventoryBalanceServiceInterface
{
    public function recalculate(
        string $productId,
        int $warehouseId,
        ?string $variantId = null,
    ): InventoryBalance;

    public function rebuildFromLedger(
        ?string $productId = null,
        ?int $warehouseId = null,
    ): int;

    public function getByProductAndWarehouse(
        string $productId,
        int $warehouseId,
        ?string $variantId = null,
    ): ?InventoryBalance;
}
