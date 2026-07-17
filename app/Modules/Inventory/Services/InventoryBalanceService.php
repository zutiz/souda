<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Models\StockReservation;

class InventoryBalanceService
{
    public function recalculate(
        string $productId,
        int $warehouseId,
        ?string $variantId = null,
    ): InventoryBalance {
        $totals = InventoryLedger::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->when($variantId, fn ($q) => $q->where('variant_id', $variantId))
            ->selectRaw('COALESCE(SUM(quantity), 0) as total_quantity')
            ->selectRaw('COALESCE(SUM(CASE WHEN quantity > 0 THEN total_cost ELSE 0 END), 0) as inbound_cost')
            ->selectRaw('COALESCE(SUM(CASE WHEN quantity < 0 THEN total_cost ELSE 0 END), 0) as outbound_cost')
            ->first();

        $lastMovement = InventoryLedger::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->when($variantId, fn ($q) => $q->where('variant_id', $variantId))
            ->orderBy('created_at', 'desc')
            ->first();

        $quantity = (int) $totals->total_quantity;
        $totalStockValue = max(0, (int) $totals->inbound_cost - (int) $totals->outbound_cost);
        $avgCost = $quantity > 0 ? (int) round($totalStockValue / $quantity) : 0;

        $reservedQuantity = (int) StockReservation::where([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'status' => 'active',
        ])
            ->when($variantId, fn ($q) => $q->where('variant_id', $variantId))
            ->sum('quantity');

        return InventoryBalance::updateOrCreate(
            [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
            ],
            [
                'quantity' => $quantity,
                'reserved_quantity' => $reservedQuantity,
                'available_quantity' => $quantity - $reservedQuantity,
                'average_unit_cost' => $avgCost,
                'total_stock_value' => $totalStockValue,
                'last_movement_at' => $lastMovement?->created_at,
            ]
        );
    }

    public function rebuildFromLedger(
        ?string $productId = null,
        ?int $warehouseId = null,
    ): int {
        $query = InventoryLedger::query()
            ->selectRaw('product_id, COALESCE(variant_id, \'\') as variant_key, warehouse_id')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->selectRaw('COALESCE(SUM(CASE WHEN quantity > 0 THEN total_cost ELSE 0 END), 0) as inbound_cost')
            ->selectRaw('COALESCE(SUM(CASE WHEN quantity < 0 THEN total_cost ELSE 0 END), 0) as outbound_cost')
            ->groupBy('product_id', 'variant_key', 'warehouse_id');

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $groups = $query->get();
        $count = 0;

        foreach ($groups as $group) {
            $variantId = $group->variant_key !== '' ? $group->variant_key : null;
            $quantity = (int) $group->total_quantity;
            $totalStockValue = max(0, (int) $group->inbound_cost - (int) $group->outbound_cost);
            $avgCost = $quantity > 0 ? (int) round($totalStockValue / $quantity) : 0;

            $reservedQuantity = (int) StockReservation::where([
                'product_id' => $group->product_id,
                'warehouse_id' => $group->warehouse_id,
                'status' => 'active',
            ])
                ->when($variantId, fn ($q) => $q->where('variant_id', $variantId))
                ->sum('quantity');

            InventoryBalance::updateOrCreate(
                [
                    'product_id' => $group->product_id,
                    'variant_id' => $variantId,
                    'warehouse_id' => $group->warehouse_id,
                ],
                [
                    'quantity' => $quantity,
                    'reserved_quantity' => $reservedQuantity,
                    'available_quantity' => $quantity - $reservedQuantity,
                    'average_unit_cost' => $avgCost,
                    'total_stock_value' => $totalStockValue,
                ]
            );

            $count++;
        }

        return $count;
    }

    public function getByProductAndWarehouse(
        string $productId,
        int $warehouseId,
        ?string $variantId = null,
    ): ?InventoryBalance {
        return InventoryBalance::where([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ])
            ->where('variant_id', $variantId)
            ->first();
    }
}
