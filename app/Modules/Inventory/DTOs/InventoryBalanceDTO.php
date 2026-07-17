<?php

declare(strict_types=1);

namespace App\Modules\Inventory\DTOs;

final class InventoryBalanceDTO
{
    public function __construct(
        public readonly string $productId,
        public readonly ?string $variantId,
        public readonly string $warehouseId,
        public readonly int $quantity,
        public readonly int $reservedQuantity,
        public readonly int $availableQuantity,
        public readonly int $averageUnitCost,
        public readonly int $totalStockValue,
        public readonly ?string $lastMovementAt = null,
    ) {}
}
