<?php

declare(strict_types=1);

namespace App\Modules\Product\Events;

use Carbon\CarbonImmutable;

readonly class VariantDeleted
{
    public function __construct(
        public string $variantId,
        public string $productId,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
