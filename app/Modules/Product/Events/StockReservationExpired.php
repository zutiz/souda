<?php

declare(strict_types=1);

namespace App\Modules\Product\Events;

use Carbon\CarbonImmutable;

readonly class StockReservationExpired
{
    public function __construct(
        public int $reservationId,
        public string $productId,
        public ?string $variantId,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
