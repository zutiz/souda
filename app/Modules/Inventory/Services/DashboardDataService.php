<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Models\DemandForecast;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryLedger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardDataService
{
    public function __construct(
        protected StockClassificationService $classificationService,
        protected AlertEngine $alertEngine,
        protected ReorderEngine $reorderEngine,
    ) {}

    public function getMovementTrend(int $days = 30, ?int $warehouseId = null): Collection
    {
        $cutoff = now()->subDays($days);

        return InventoryLedger::where('created_at', '>=', $cutoff)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(CASE WHEN quantity > 0 THEN quantity ELSE 0 END) as quantity_in')
            ->selectRaw('SUM(CASE WHEN quantity < 0 THEN ABS(quantity) ELSE 0 END) as quantity_out')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'quantity_in' => (int) $row->quantity_in,
                'quantity_out' => (int) $row->quantity_out,
                'net_movement' => (int) $row->quantity_in - (int) $row->quantity_out,
            ]);
    }

    public function getStockValueTrend(int $days = 30, ?int $warehouseId = null): Collection
    {
        $cutoff = now()->subDays($days);

        $currentValue = (int) InventoryBalance::when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->sum('total_stock_value');

        $dailyChanges = InventoryLedger::where('created_at', '>=', $cutoff)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(COALESCE(total_cost, unit_cost * quantity, 0)) as net_value_change')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'net_value_change' => (int) $row->net_value_change,
            ]);

        $running = $currentValue;
        $result = collect();

        foreach ($dailyChanges->reverse() as $change) {
            $result->push(['date' => $change['date'], 'value' => $running]);
            $running -= $change['net_value_change'];
        }

        if ($result->isNotEmpty()) {
            $result->push(['date' => now()->format('Y-m-d'), 'value' => $currentValue]);
        }

        return $result;
    }

    public function getTopMovingProducts(int $limit = 10, int $days = 30, ?int $warehouseId = null): Collection
    {
        $cutoff = now()->subDays($days);

        return InventoryLedger::where('quantity', '<', 0)
            ->where('created_at', '>=', $cutoff)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->selectRaw('product_id')
            ->selectRaw('SUM(ABS(quantity)) as total_out')
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as movement_days')
            ->groupBy('product_id')
            ->orderByDesc('total_out')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'product_id' => $row->product_id,
                'product_name' => null,
                'sku' => null,
                'total_out' => (int) $row->total_out,
                'movement_days' => (int) $row->movement_days,
            ]);
    }

    public function getClassificationDistribution(): array
    {
        return $this->classificationService->getClassificationStats();
    }

    public function getDeadStockTrend(int $days = 90, ?int $warehouseId = null): Collection
    {
        $deadDays = (int) config('inventory.dead_stock_days', 90);
        $records = collect();
        $interval = max(1, (int) ($days / 10));

        for ($i = 0; $i <= $days; $i += $interval) {
            $cutoffDate = now()->subDays($i);
            $cutoffMovement = now()->subDays($deadDays + $i);

            $count = (int) InventoryBalance::where('quantity', '>', 0)
                ->where(function ($q) use ($cutoffMovement) {
                    $q->whereNull('last_movement_at')
                        ->orWhere('last_movement_at', '<', $cutoffMovement);
                })
                ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                ->count();

            $value = (int) InventoryBalance::where('quantity', '>', 0)
                ->where(function ($q) use ($cutoffMovement) {
                    $q->whereNull('last_movement_at')
                        ->orWhere('last_movement_at', '<', $cutoffMovement);
                })
                ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                ->sum('total_stock_value');

            $records->push([
                'date' => $cutoffDate->format('Y-m-d'),
                'dead_stock_count' => $count,
                'dead_stock_value' => $value,
            ]);
        }

        return $records;
    }

    public function getHealthScore(?int $warehouseId = null): array
    {
        $totalBalances = InventoryBalance::when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))->count();
        $totalBalances = max($totalBalances, 1);

        $lowStockCount = $this->alertEngine->findLowStock($warehouseId)->count();
        $lowStockRatio = $lowStockCount / $totalBalances;

        $deadDays = (int) config('inventory.dead_stock_days', 90);
        $deadStockCount = (int) InventoryBalance::where('quantity', '>', 0)
            ->where(function ($q) use ($deadDays) {
                $q->whereNull('last_movement_at')
                    ->orWhere('last_movement_at', '<', now()->subDays($deadDays));
            })
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->count();
        $deadStockRatio = $deadStockCount / $totalBalances;

        $velocityDays = (int) config('inventory.sales_velocity_days', 30);
        $allBalances = InventoryBalance::where('quantity', '>', 0)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->get();

        $velocities = $allBalances->map(
            fn ($b) => $this->reorderEngine->calculateSalesVelocity($b->product_id, $b->warehouse_id, $velocityDays)
        );

        $avgVelocity = $velocities->isNotEmpty() ? $velocities->average() : 0;

        $lowStockScore = max(0, 100 - ($lowStockRatio * 100));
        $deadStockScore = max(0, 100 - ($deadStockRatio * 100));
        $velocityScore = min(100, ($avgVelocity / 10) * 100);

        $score = (int) round(($lowStockScore * 0.4) + ($deadStockScore * 0.35) + ($velocityScore * 0.25));

        $grade = match (true) {
            $score >= 80 => 'healthy',
            $score >= 50 => 'fair',
            default => 'critical',
        };

        return [
            'score' => min(100, max(0, $score)),
            'grade' => $grade,
            'low_stock_ratio' => round($lowStockRatio, 3),
            'dead_stock_ratio' => round($deadStockRatio, 3),
            'avg_velocity' => round($avgVelocity, 2),
        ];
    }

    public function getForecastAccuracy(): Collection
    {
        return DemandForecast::whereNotNull('accuracy_score')
            ->selectRaw('model_used')
            ->selectRaw('ROUND(AVG(accuracy_score), 1) as avg_accuracy')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('model_used')
            ->get()
            ->map(fn ($row) => [
                'model_used' => $row->model_used,
                'avg_accuracy' => (float) $row->avg_accuracy,
                'count' => (int) $row->count,
            ]);
    }

    public function getDashboardData(int $days = 30, ?int $warehouseId = null): array
    {
        return [
            'movement_trend' => $this->getMovementTrend($days, $warehouseId),
            'stock_value_trend' => $this->getStockValueTrend($days, $warehouseId),
            'top_moving_products' => $this->getTopMovingProducts(limit: 10, days: $days, warehouseId: $warehouseId),
            'classification_distribution' => $this->getClassificationDistribution(),
            'dead_stock_trend' => $this->getDeadStockTrend(days: $days, warehouseId: $warehouseId),
            'health_score' => $this->getHealthScore($warehouseId),
            'forecast_accuracy' => $this->getForecastAccuracy(),
        ];
    }
}
