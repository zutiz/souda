<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use App\Modules\Inventory\Models\InventoryCountItem;
use Carbon\CarbonImmutable;

readonly class CountItemRecorded
{
    public function __construct(
        public InventoryCountItem $item,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
