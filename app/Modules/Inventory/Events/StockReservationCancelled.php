<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use Carbon\CarbonImmutable;

readonly class StockReservationCancelled
{
    public function __construct(
        public int $reservationId,
        public string $productId,
        public ?string $variantId,
        public int $warehouseId,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
