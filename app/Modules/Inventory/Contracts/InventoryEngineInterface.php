<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Contracts;

use App\Modules\Inventory\Models\InventoryLedger;

interface InventoryEngineInterface
{
    public function recordMovement(
        string $productId,
        ?string $variantId,
        int $warehouseId,
        int $quantity,
        string $movementType,
        string $reference,
        ?int $unitCost = null,
        ?int $batchId = null,
        ?array $serialNumbers = null,
        ?string $description = null,
    ): InventoryLedger;

    public function getBalance(
        string $productId,
        int $warehouseId,
        ?string $variantId = null,
    ): int;
}
