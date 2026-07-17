<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Costing\Strategies;

use App\Modules\Inventory\Models\CostLayer;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Services\Costing\Contracts\CostingStrategyInterface;

class LifoCosting implements CostingStrategyInterface
{
    public function processInbound(InventoryLedger $ledger): void
    {
        if ($ledger->unit_cost === null) {
            return;
        }

        CostLayer::create([
            'product_id' => $ledger->product_id,
            'variant_id' => $ledger->variant_id,
            'warehouse_id' => $ledger->warehouse_id,
            'unit_cost' => $ledger->unit_cost,
            'quantity_remaining' => $ledger->quantity,
            'quantity_original' => $ledger->quantity,
            'costing_method' => 'lifo',
            'ledger_entry_id' => $ledger->id,
        ]);

        $balance = InventoryBalance::where([
            'product_id' => $ledger->product_id,
            'variant_id' => $ledger->variant_id,
            'warehouse_id' => $ledger->warehouse_id,
        ])->first();

        if ($balance) {
            $balance->updateQuietly([
                'total_stock_value' => ($balance->total_stock_value ?? 0) + ($ledger->quantity * $ledger->unit_cost),
            ]);
        }
    }

    public function processOutbound(InventoryLedger $ledger): void
    {
        $outboundQty = abs($ledger->quantity);
        $remaining = $outboundQty;
        $totalCost = 0;

        $layers = CostLayer::where('product_id', $ledger->product_id)
            ->where('warehouse_id', $ledger->warehouse_id)
            ->where('variant_id', $ledger->variant_id)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('id', 'desc')
            ->get();

        foreach ($layers as $layer) {
            if ($remaining <= 0) {
                break;
            }

            $consume = min($remaining, $layer->quantity_remaining);
            $layer->decrement('quantity_remaining', $consume);
            $totalCost += $consume * $layer->unit_cost;
            $remaining -= $consume;
        }

        if ($outboundQty > 0) {
            $avgCost = (int) round($totalCost / $outboundQty);
            $ledger->updateQuietly(['total_cost' => $totalCost]);

            $balance = InventoryBalance::where([
                'product_id' => $ledger->product_id,
                'variant_id' => $ledger->variant_id,
                'warehouse_id' => $ledger->warehouse_id,
            ])->first();

            if ($balance) {
                $balance->updateQuietly([
                    'average_unit_cost' => $avgCost,
                    'total_stock_value' => max(0, $balance->total_stock_value - $totalCost),
                ]);
            }
        }
    }
}
