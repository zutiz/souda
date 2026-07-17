<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Contracts;

use App\Modules\Inventory\Enums\MovementTypeEnum;
use App\Modules\Inventory\Models\InventoryLedger;
use Illuminate\Support\Collection;

interface StockMovementEngineInterface
{
    public function record(
        string $productId,
        ?string $variantId,
        int $warehouseId,
        int $quantity,
        MovementTypeEnum $type,
        string $reference,
        ?int $unitCost = null,
        ?string $batchId = null,
        ?array $serialNumbers = null,
        ?string $description = null,
        ?string $createdBy = null,
        array $metadata = [],
    ): InventoryLedger;

    public function generateReference(MovementTypeEnum $type): string;

    public function findByReference(string $reference): Collection;

    public function findByProduct(
        string $productId,
        ?int $warehouseId = null,
        ?string $movementType = null,
        ?string $from = null,
        ?string $to = null,
        ?int $limit = null,
    ): Collection;
}
