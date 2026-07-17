<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Events\LowStockAlert;
use App\Modules\Inventory\Events\StockDepleted;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Product\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class AlertEngine
{
    public function evaluate(string $productId, int $warehouseId, ?string $variantId = null): void
    {
        $balance = InventoryBalance::where([
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ])->where('variant_id', $variantId)->first();

        if ($balance === null) {
            return;
        }

        $product = Product::find($productId);

        if ($product !== null && $balance->quantity <= 0 && $balance->getOriginal('quantity') > 0) {
            StockDepleted::dispatch($productId, (string) $warehouseId, $variantId);
        }

        if ($product !== null && $balance->quantity <= (int) $product->low_stock_threshold) {
            LowStockAlert::dispatch(
                productId: $productId,
                warehouseId: (string) $warehouseId,
                currentQuantity: $balance->quantity,
                threshold: (int) $product->low_stock_threshold,
            );
        }
    }

    public function findLowStock(?int $warehouseId = null): Collection
    {
        $balances = InventoryBalance::where('quantity', '>', 0)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->get();

        return $balances->filter(function (InventoryBalance $balance) {
            $product = Product::find($balance->product_id);

            return $product !== null && $balance->quantity <= $product->low_stock_threshold;
        });
    }

    public function findDeadStock(int $days = 90, ?int $warehouseId = null): Collection
    {
        $cutoff = now()->subDays($days);

        return InventoryBalance::where('quantity', '>', 0)
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('last_movement_at')
                    ->orWhere('last_movement_at', '<', $cutoff);
            })
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->get();
    }

    public function findOverstock(int $threshold = 1000, ?int $warehouseId = null): Collection
    {
        return InventoryBalance::where('quantity', '>', $threshold)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->get();
    }

    public function getDashboardStats(): array
    {
        $totalStockValue = (int) InventoryBalance::sum('total_stock_value');

        $todayMovementsIn = (int) InventoryLedger::whereDate('created_at', today())
            ->where('quantity', '>', 0)
            ->sum('quantity');

        $todayMovementsOut = (int) InventoryLedger::whereDate('created_at', today())
            ->where('quantity', '<', 0)
            ->sum('quantity');

        $lowStockCount = $this->findLowStock()->count();

        $expiringCount = InventoryBatch::expiring(
            (int) config('inventory.expiry_alert_days', 30),
        )->count();

        return [
            'total_stock_value' => $totalStockValue,
            'today_movements_in' => $todayMovementsIn,
            'today_movements_out' => abs($todayMovementsOut),
            'low_stock_count' => $lowStockCount,
            'expiring_count' => $expiringCount,
        ];
    }
}
