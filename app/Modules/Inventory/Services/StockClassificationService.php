<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\AbcClassEnum;
use App\Modules\Inventory\Enums\VelocityClassEnum;
use App\Modules\Inventory\Models\InventoryBalance;
use Illuminate\Support\Facades\DB;

class StockClassificationService
{
    public function __construct(
        protected ReorderEngine $reorderEngine,
    ) {}

    public function classifyAll(?int $warehouseId = null): array
    {
        $abcCount = $this->classifyAbc($warehouseId);
        $velocityCount = $this->classifyVelocity($warehouseId);

        return [
            'abc' => $abcCount,
            'velocity' => $velocityCount,
        ];
    }

    public function classifyAbc(?int $warehouseId = null): array
    {
        $query = InventoryBalance::where('quantity', '>', 0)
            ->select('id', 'product_id', 'warehouse_id', 'total_stock_value')
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->orderByDesc('total_stock_value');

        $balances = $query->get();

        if ($balances->isEmpty()) {
            return ['a' => 0, 'b' => 0, 'c' => 0];
        }

        $totalValue = (int) $balances->sum('total_stock_value');

        if ($totalValue === 0) {
            return ['a' => 0, 'b' => 0, 'c' => 0];
        }

        $cumulative = 0;
        $counts = ['a' => 0, 'b' => 0, 'c' => 0];

        foreach ($balances as $balance) {
            $cumulative += (int) $balance->total_stock_value;
            $ratio = $cumulative / $totalValue;

            $class = match (true) {
                $ratio <= 0.80 => AbcClassEnum::A,
                $ratio <= 0.95 => AbcClassEnum::B,
                default => AbcClassEnum::C,
            };

            InventoryBalance::where('id', $balance->id)->update(['abc_class' => $class->value]);
            $counts[$class->value]++;
        }

        return $counts;
    }

    public function classifyVelocity(?int $warehouseId = null): array
    {
        $days = (int) config('inventory.sales_velocity_days', 30);
        $deadDays = (int) config('inventory.dead_stock_days', 90);
        $slowThreshold = (float) config('inventory.velocity_slow_threshold', 1.0);
        $fastThreshold = (float) config('inventory.velocity_fast_threshold', 10.0);

        $balances = InventoryBalance::where('quantity', '>', 0)
            ->select('id', 'product_id', 'warehouse_id', 'last_movement_at')
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->get();

        if ($balances->isEmpty()) {
            return ['fast' => 0, 'slow' => 0, 'dead' => 0, 'new' => 0];
        }

        $counts = ['fast' => 0, 'slow' => 0, 'dead' => 0, 'new' => 0];

        foreach ($balances as $balance) {
            $velocity = $this->reorderEngine->calculateSalesVelocity(
                productId: $balance->product_id,
                warehouseId: $balance->warehouse_id,
                days: $days,
            );

            $lastMovement = $balance->last_movement_at;
            $hasZeroVelocity = $velocity === 0.0;

            if ($hasZeroVelocity || ($lastMovement !== null && $lastMovement->lt(now()->subDays($deadDays)))) {
                $class = VelocityClassEnum::Dead;
            } elseif ($lastMovement === null) {
                $class = VelocityClassEnum::New;
            } elseif ($velocity >= $fastThreshold) {
                $class = VelocityClassEnum::Fast;
            } elseif ($velocity >= $slowThreshold) {
                $class = VelocityClassEnum::Slow;
            } else {
                $class = VelocityClassEnum::Dead;
            }

            InventoryBalance::where('id', $balance->id)->update(['velocity_class' => $class->value]);
            $counts[$class->value]++;
        }

        return $counts;
    }

    public function getClassificationStats(): array
    {
        $abcCounts = InventoryBalance::whereNotNull('abc_class')
            ->select('abc_class', DB::raw('count(*) as total'))
            ->groupBy('abc_class')
            ->pluck('total', 'abc_class')
            ->toArray();

        $velocityCounts = InventoryBalance::whereNotNull('velocity_class')
            ->select('velocity_class', DB::raw('count(*) as total'))
            ->groupBy('velocity_class')
            ->pluck('total', 'velocity_class')
            ->toArray();

        return [
            'abc' => [
                'a' => $abcCounts['a'] ?? 0,
                'b' => $abcCounts['b'] ?? 0,
                'c' => $abcCounts['c'] ?? 0,
            ],
            'velocity' => [
                'fast' => $velocityCounts['fast'] ?? 0,
                'slow' => $velocityCounts['slow'] ?? 0,
                'dead' => $velocityCounts['dead'] ?? 0,
                'new' => $velocityCounts['new'] ?? 0,
            ],
        ];
    }

    public function getClassifiedBalances(
        ?string $abcClass = null,
        ?string $velocityClass = null,
        ?int $warehouseId = null,
        ?string $search = null,
    ) {
        return InventoryBalance::with(['product:id,name,sku', 'warehouse:id,name'])
            ->where('quantity', '>', 0)
            ->when($abcClass, fn ($q) => $q->where('abc_class', $abcClass))
            ->when($velocityClass, fn ($q) => $q->where('velocity_class', $velocityClass))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('product', fn ($pq) => $pq
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                );
            })
            ->latest()
            ->paginate(25);
    }
}
