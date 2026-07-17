<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use Carbon\CarbonImmutable;

readonly class BatchDepleted
{
    public function __construct(
        public int $batchId,
        public string $productId,
        public string $batchNumber,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
