<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use App\Modules\Inventory\Models\InventoryBalance;
use Illuminate\Foundation\Events\Dispatchable;

class InventoryBalanceUpdated
{
    use Dispatchable;

    public function __construct(
        public readonly InventoryBalance $balance,
        public readonly int $previousQuantity,
    ) {}
}
