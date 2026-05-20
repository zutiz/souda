<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Enums\MovementTypeEnum;
use App\Modules\Product\Models\StockMovement;

readonly class StockMovementDTO
{
    public function __construct(
        public ?string $id,
        public int $warehouseId,
        public ?string $productId,
        public ?string $variantId,
        public MovementTypeEnum $movementType,
        public int $quantity,
        public ?string $referenceType,
        public ?int $referenceId,
        public ?string $notes,
        public ?int $performedBy,
    ) {}

    public static function fromModel(StockMovement $movement): self
    {
        return new self(
            id: $movement->id,
            warehouseId: $movement->warehouse_id,
            productId: $movement->product_id,
            variantId: $movement->variant_id,
            movementType: $movement->movement_type instanceof MovementTypeEnum
                ? $movement->movement_type
                : MovementTypeEnum::from($movement->movement_type),
            quantity: $movement->quantity,
            referenceType: $movement->reference_type,
            referenceId: $movement->reference_id,
            notes: $movement->notes,
            performedBy: $movement->performed_by,
        );
    }
}
