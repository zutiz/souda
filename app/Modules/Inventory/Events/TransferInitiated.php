<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use Carbon\CarbonImmutable;

readonly class TransferInitiated
{
    public function __construct(
        public int $transferId,
        public int $fromWarehouseId,
        public int $toWarehouseId,
        public int $itemCount,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
