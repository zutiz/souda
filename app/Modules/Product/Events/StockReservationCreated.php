<?php

declare(strict_types=1);

namespace App\Modules\Product\Events;

use Carbon\CarbonImmutable;

readonly class StockReservationCreated
{
    public function __construct(
        public int $reservationId,
        public string $productId,
        public ?string $variantId,
        public int $quantity,
        public string $referenceType,
        public int $referenceId,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
