<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Models\WarehouseStock;

readonly class WarehouseStockDTO
{
    public function __construct(
        public ?int $id,
        public int $warehouseId,
        public ?string $productId,
        public ?string $variantId,
        public int $quantity,
        public int $reservedQuantity,
        public int $availableQuantity,
        public int $reorderLevel,
    ) {}

    public static function fromModel(WarehouseStock $stock): self
    {
        return new self(
            id: $stock->id,
            warehouseId: $stock->warehouse_id,
            productId: $stock->product_id,
            variantId: $stock->variant_id,
            quantity: $stock->quantity,
            reservedQuantity: $stock->reserved_quantity,
            availableQuantity: $stock->getAvailableQuantity(),
            reorderLevel: $stock->reorder_level,
        );
    }
}
