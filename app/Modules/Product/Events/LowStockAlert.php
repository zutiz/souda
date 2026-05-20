<?php

declare(strict_types=1);

namespace App\Modules\Product\Events;

use Carbon\CarbonImmutable;

readonly class LowStockAlert
{
    public function __construct(
        public string $productId,
        public ?string $variantId,
        public int $warehouseId,
        public int $availableQuantity,
        public int $threshold,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
