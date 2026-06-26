<?php

declare(strict_types=1);

namespace App\Modules\Product\Events;

use Carbon\CarbonImmutable;

readonly class StockTransferCompleted
{
    public function __construct(
        public string $productId,
        public ?string $variantId,
        public int $fromWarehouseId,
        public int $toWarehouseId,
        public int $quantity,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
