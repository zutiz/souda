<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Costing\Contracts;

use App\Modules\Inventory\Models\InventoryLedger;

interface CostingStrategyInterface
{
    public function processInbound(InventoryLedger $ledger): void;

    public function processOutbound(InventoryLedger $ledger): void;
}
