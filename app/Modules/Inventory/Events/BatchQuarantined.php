<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use Carbon\CarbonImmutable;

readonly class BatchQuarantined
{
    public function __construct(
        public int $batchId,
        public string $productId,
        public string $batchNumber,
        public ?string $reason = null,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
