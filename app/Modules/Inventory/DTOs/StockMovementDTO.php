<?php

declare(strict_types=1);

namespace App\Modules\Inventory\DTOs;

use App\Modules\Inventory\Enums\MovementTypeEnum;

final class StockMovementDTO
{
    public function __construct(
        public readonly string $productId,
        public readonly ?string $variantId,
        public readonly int $warehouseId,
        public readonly int $quantity,
        public readonly MovementTypeEnum $type,
        public readonly string $reference,
        public readonly string $referenceType,
        public readonly ?int $unitCost = null,
        public readonly ?string $batchId = null,
        public readonly ?array $serialNumbers = null,
        public readonly ?string $description = null,
        public readonly ?string $createdBy = null,
        public readonly array $metadata = [],
    ) {}
}
