<?php

declare(strict_types=1);

namespace App\Modules\Product\Events;

use Carbon\CarbonImmutable;

readonly class ProductDeleted
{
    public function __construct(
        public string $productId,
        public ?string $sku,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
