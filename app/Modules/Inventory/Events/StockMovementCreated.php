<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Events;

use App\Modules\Inventory\Models\InventoryLedger;
use Illuminate\Foundation\Events\Dispatchable;

class StockMovementCreated
{
    use Dispatchable;

    public function __construct(
        public readonly InventoryLedger $ledger,
    ) {}
}
