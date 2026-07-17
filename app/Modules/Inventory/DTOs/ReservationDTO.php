<?php

declare(strict_types=1);

namespace App\Modules\Inventory\DTOs;

final class ReservationDTO
{
    public function __construct(
        public readonly string $productId,
        public readonly ?string $variantId,
        public readonly string $warehouseId,
        public readonly int $quantity,
        public readonly string $reference,
        public readonly string $referenceType,
        public readonly ?int $ttlMinutes = null,
    ) {}
}
