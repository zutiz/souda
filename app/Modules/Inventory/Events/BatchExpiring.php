<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use Carbon\CarbonImmutable;

readonly class BatchExpiring
{
    public function __construct(
        public int $batchId,
        public string $productId,
        public CarbonImmutable $expiryDate,
        public int $daysRemaining,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
