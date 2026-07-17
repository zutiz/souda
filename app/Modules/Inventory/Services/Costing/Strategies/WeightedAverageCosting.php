<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Costing\Strategies;

use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Services\Costing\Contracts\CostingStrategyInterface;

class WeightedAverageCosting implements CostingStrategyInterface
{
    public function processInbound(InventoryLedger $ledger): void
    {
        if ($ledger->unit_cost === null) {
            return;
        }

        $balance = InventoryBalance::where([
            'product_id' => $ledger->product_id,
            'variant_id' => $ledger->variant_id,
            'warehouse_id' => $ledger->warehouse_id,
        ])->first();

        $currentQty = $balance?->quantity ?? 0;
        $currentAvgCost = $balance?->average_unit_cost ?? 0;
        $inboundQty = $ledger->quantity;
        $inboundCost = $ledger->unit_cost;

        $totalQty = $currentQty + $inboundQty;
        $totalCost = ($currentQty * $currentAvgCost) + ($inboundQty * $inboundCost);
        $newAvgCost = $totalQty > 0 ? (int) round($totalCost / $totalQty) : 0;

        if ($balance) {
            $balance->updateQuietly([
                'average_unit_cost' => $newAvgCost,
                'total_stock_value' => $totalCost,
            ]);
        }
    }

    public function processOutbound(InventoryLedger $ledger): void
    {
        $balance = InventoryBalance::where([
            'product_id' => $ledger->product_id,
            'variant_id' => $ledger->variant_id,
            'warehouse_id' => $ledger->warehouse_id,
        ])->first();

        if ($balance === null || $balance->average_unit_cost === 0) {
            return;
        }

        $outboundQty = abs($ledger->quantity);
        $totalCost = $outboundQty * $balance->average_unit_cost;

        $ledger->updateQuietly(['total_cost' => $totalCost]);

        $balance->updateQuietly([
            'total_stock_value' => max(0, $balance->total_stock_value - $totalCost),
        ]);
    }
}
