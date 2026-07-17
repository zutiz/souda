<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use Carbon\CarbonImmutable;

readonly class SerialNumberSold
{
    public function __construct(
        public int $serialId,
        public string $serialNumber,
        public string $productId,
        public string $orderReference,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
