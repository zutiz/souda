<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use App\Modules\Inventory\Models\InventoryCount;
use Carbon\CarbonImmutable;

readonly class CountAdjustmentsApplied
{
    public function __construct(
        public InventoryCount $count,
        public int $adjustedCount,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
