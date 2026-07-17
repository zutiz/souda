<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use App\Modules\Inventory\Models\InventoryCount;
use Carbon\CarbonImmutable;

readonly class CountCreated
{
    public function __construct(
        public InventoryCount $count,
        public CarbonImmutable $occurredAt = new CarbonImmutable,
    ) {}
}
