<?php

declare(strict_types=1);

namespace App\Modules\Inventory\DTOs;

use App\Modules\Inventory\Enums\CostingMethodEnum;

final class CostLayerDTO
{
    public function __construct(
        public readonly string $productId,
        public readonly ?string $variantId,
        public readonly string $warehouseId,
        public readonly int $unitCost,
        public readonly int $quantityRemaining,
        public readonly int $quantityOriginal,
        public readonly CostingMethodEnum $costingMethod,
    ) {}
}
