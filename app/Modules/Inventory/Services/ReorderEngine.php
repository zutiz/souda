<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\DemandForecast;
use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Models\PurchaseSuggestion;
use App\Modules\Inventory\Services\Forecasting\DemandForecastService;
use Illuminate\Database\Eloquent\Collection;

class ReorderEngine
{
    public function __construct(
        protected AlertEngine $alertEngine,
        protected DemandForecastService $forecastService,
    ) {}

    public function generateSuggestions(?int $warehouseId = null, bool $useForecast = true): int
    {
        $count = 0;
        $lowStock = $this->alertEngine->findLowStock($warehouseId);

        $lowStock->loadMissing('product');

        foreach ($lowStock as $balance) {
            $product = $balance->product;

            if ($product === null || ! $product->track_inventory) {
                continue;
            }

            $salesVelocity = $this->calculateSalesVelocity(
                productId: $balance->product_id,
                warehouseId: $balance->warehouse_id,
                days: (int) config('inventory.sales_velocity_days', 30),
            );

            $forecastDemand = $useForecast
                ? $this->getForecastedDemand($balance->product_id, $balance->warehouse_id)
                : null;

            $effectiveVelocity = $forecastDemand ?? $salesVelocity;

            $leadTime = (int) ($product->lead_time_days ?? config('inventory.default_lead_time_days', 7));
            $safetyStock = (int) ($product->safety_stock ?? config('inventory.default_safety_stock', 0));
            $reorderLevel = max(1, (int) $product->low_stock_threshold);

            $suggestedQty = $this->calculateReorderQuantity(
                currentQuantity: $balance->quantity,
                reservedQuantity: $balance->reserved_quantity,
                reorderLevel: $reorderLevel,
                leadTimeDays: $leadTime,
                safetyStock: $safetyStock,
                salesVelocity: $effectiveVelocity,
            );

            if ($suggestedQty <= 0) {
                continue;
            }

            PurchaseSuggestion::updateOrCreate(
                [
                    'product_id' => $balance->product_id,
                    'variant_id' => $balance->variant_id,
                    'warehouse_id' => $balance->warehouse_id,
                    'status' => 'pending',
                ],
                [
                    'current_quantity' => $balance->quantity,
                    'reserved_quantity' => $balance->reserved_quantity,
                    'available_quantity' => $balance->getAvailableQuantity(),
                    'reorder_level' => $reorderLevel,
                    'lead_time_days' => $leadTime,
                    'safety_stock' => $safetyStock,
                    'sales_velocity' => $salesVelocity,
                    'suggested_quantity' => $suggestedQty,
                ],
            );

            $count++;
        }

        return $count;
    }

    public function calculateSalesVelocity(string $productId, int $warehouseId, int $days = 30): float
    {
        $cutoff = now()->subDays($days);

        $totalOut = (int) InventoryLedger::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '<', 0)
            ->where('created_at', '>=', $cutoff)
            ->sum('quantity');

        if ($totalOut === 0) {
            return 0;
        }

        $movementDays = (int) InventoryLedger::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '<', 0)
            ->where('created_at', '>=', $cutoff)
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as days')
            ->value('days');

        $effectiveDays = max($movementDays, 1);

        return round(abs($totalOut) / $effectiveDays, 2);
    }

    public function getForecastedDemand(string $productId, int $warehouseId): ?float
    {
        $latestForecast = DemandForecast::byProduct($productId, $warehouseId)
            ->whereNull('actual_quantity')
            ->where('forecast_date', '>=', now())
            ->latest('forecast_date')
            ->first();

        if ($latestForecast === null) {
            return null;
        }

        $daysInPeriod = $latestForecast->period_start->diffInDays($latestForecast->period_end) ?: 1;

        return round($latestForecast->forecast_quantity / $daysInPeriod, 2);
    }

    public function calculateReorderQuantity(
        int $currentQuantity,
        int $reservedQuantity,
        int $reorderLevel,
        int $leadTimeDays,
        int $safetyStock,
        float $salesVelocity,
    ): int {
        $available = $currentQuantity - $reservedQuantity;
        $leadTimeDemand = (int) ceil($salesVelocity * $leadTimeDays);
        $targetStock = $reorderLevel + $leadTimeDemand + $safetyStock;
        $maxOrder = (int) config('inventory.reorder_max_quantity', 10000);

        $suggested = max(0, $targetStock - $available);

        return min($suggested, $maxOrder);
    }

    public function getDemandHistory(string $productId, int $warehouseId, int $months = 12): Collection
    {
        $cutoff = now()->subMonths($months);

        $ledgerData = InventoryLedger::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '<', 0)
            ->where('created_at', '>=', $cutoff)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period")
            ->selectRaw('SUM(quantity) as total_out')
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as movement_days')
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => [
                'period' => $row->period,
                'quantity' => abs((int) $row->total_out),
                'movement_days' => (int) $row->movement_days,
            ]);

        return $ledgerData;
    }

    public function getPendingSuggestions(?int $warehouseId = null): Collection
    {
        return PurchaseSuggestion::with(['product:id,name,sku', 'warehouse:id,name'])
            ->where('status', 'pending')
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->latest()
            ->get();
    }

    public function dismiss(PurchaseSuggestion $suggestion, ?string $notes = null): void
    {
        $suggestion->update([
            'status' => 'dismissed',
            'notes' => $notes,
        ]);
    }

    public function markOrdered(PurchaseSuggestion $suggestion, ?string $orderReference = null): void
    {
        $suggestion->update([
            'status' => 'ordered',
            'order_reference' => $orderReference,
        ]);
    }

    public function dismissByProduct(string $productId, int $warehouseId, ?string $notes = null): int
    {
        return PurchaseSuggestion::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('status', 'pending')
            ->update(['status' => 'dismissed', 'notes' => $notes]);
    }
}
