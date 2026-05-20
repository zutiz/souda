<?php

declare(strict_types=1);

namespace App\Modules\Product\DTOs;

use App\Modules\Product\Enums\StockReservationStatusEnum;
use App\Modules\Product\Models\StockReservation;
use Carbon\CarbonImmutable;

readonly class StockReservationDTO
{
    public function __construct(
        public ?int $id,
        public int $warehouseId,
        public ?string $productId,
        public ?string $variantId,
        public int $quantity,
        public string $referenceType,
        public int $referenceId,
        public CarbonImmutable $expiresAt,
        public StockReservationStatusEnum $status,
    ) {}

    public static function fromModel(StockReservation $reservation): self
    {
        return new self(
            id: $reservation->id,
            warehouseId: $reservation->warehouse_id,
            productId: $reservation->product_id,
            variantId: $reservation->variant_id,
            quantity: $reservation->quantity,
            referenceType: $reservation->reference_type,
            referenceId: $reservation->reference_id,
            expiresAt: CarbonImmutable::instance($reservation->expires_at),
            status: $reservation->status instanceof StockReservationStatusEnum
                ? $reservation->status
                : StockReservationStatusEnum::from($reservation->status),
        );
    }
}
