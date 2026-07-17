<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\AlertSeverityEnum;
use App\Modules\Inventory\Models\InventoryAlert;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\Inventory\Models\InventoryRule;
use Illuminate\Database\Eloquent\Collection;

class RuleEngine
{
    public function __construct(
        protected AlertEngine $alertEngine,
        protected ReorderEngine $reorderEngine,
        protected StockClassificationService $classificationService,
    ) {}

    public function evaluateAll(?int $ruleId = null, ?int $warehouseId = null): array
    {
        $rules = InventoryRule::active()
            ->when($ruleId, fn ($q) => $q->where('id', $ruleId))
            ->get();

        $results = [];

        foreach ($rules as $rule) {
            $results[$rule->id] = $this->evaluateRule($rule, $warehouseId);
        }

        return $results;
    }

    public function evaluateRule(InventoryRule $rule, ?int $warehouseId = null): array
    {
        $rule->update(['last_run_at' => now()]);

        return match ($rule->condition_type) {
            'low_stock' => $this->evaluateLowStock($rule, $warehouseId),
            'dead_stock' => $this->evaluateDeadStock($rule, $warehouseId),
            'overstock' => $this->evaluateOverstock($rule, $warehouseId),
            'expiring_batch' => $this->evaluateExpiringBatch($rule, $warehouseId),
            'slow_moving' => $this->evaluateSlowMoving($rule, $warehouseId),
            'fast_moving' => $this->evaluateFastMoving($rule, $warehouseId),
            default => ['triggered' => 0, 'errors' => ["Unknown condition type: {$rule->condition_type}"]],
        };
    }

    protected function evaluateLowStock(InventoryRule $rule, ?int $warehouseId = null): array
    {
        $threshold = (int) ($rule->condition_config['threshold'] ?? 10);
        $items = $this->alertEngine->findLowStock($warehouseId);

        return $this->processItems($rule, $items, 'low_stock', function ($item) use ($threshold) {
            return [
                'title' => "Low Stock: {$item->product?->name}",
                'message' => "Quantity ({$item->quantity}) is below threshold ({$threshold}).",
                'severity' => AlertSeverityEnum::Warning,
                'product_id' => $item->product_id,
                'warehouse_id' => $item->warehouse_id,
            ];
        });
    }

    protected function evaluateDeadStock(InventoryRule $rule, ?int $warehouseId = null): array
    {
        $days = (int) ($rule->condition_config['days'] ?? 90);
        $items = $this->alertEngine->findDeadStock(days: $days, warehouseId: $warehouseId);

        return $this->processItems($rule, $items, 'dead_stock', function ($item) use ($days) {
            $lastMovement = $item->last_movement_at?->format('Y-m-d') ?? 'Never';

            return [
                'title' => "Dead Stock: {$item->product?->name}",
                'message' => "No movement in {$days} days. Last movement: {$lastMovement}.",
                'severity' => AlertSeverityEnum::Warning,
                'product_id' => $item->product_id,
                'warehouse_id' => $item->warehouse_id,
            ];
        });
    }

    protected function evaluateOverstock(InventoryRule $rule, ?int $warehouseId = null): array
    {
        $threshold = (int) ($rule->condition_config['threshold'] ?? 1000);
        $items = $this->alertEngine->findOverstock(threshold: $threshold, warehouseId: $warehouseId);

        return $this->processItems($rule, $items, 'overstock', function ($item) use ($threshold) {
            return [
                'title' => "Overstock: {$item->product?->name}",
                'message' => "Quantity ({$item->quantity}) exceeds threshold ({$threshold}).",
                'severity' => AlertSeverityEnum::Info,
                'product_id' => $item->product_id,
                'warehouse_id' => $item->warehouse_id,
            ];
        });
    }

    protected function evaluateExpiringBatch(InventoryRule $rule, ?int $warehouseId = null): array
    {
        $days = (int) ($rule->condition_config['days'] ?? 30);

        $batches = InventoryBatch::expiring($days)
            ->with(['product:id,name,sku', 'warehouse:id,name'])
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->get();

        return $this->processItems($rule, $batches, 'expiring_batch', function ($batch) use ($days) {
            return [
                'title' => "Expiring: {$batch->product?->name} (Batch {$batch->batch_number})",
                'message' => "Expires {$batch->expiry_date?->format('Y-m-d')} (within {$days} days). Quantity: {$batch->quantity}.",
                'severity' => AlertSeverityEnum::Critical,
                'product_id' => $batch->product_id,
                'warehouse_id' => $batch->warehouse_id,
            ];
        });
    }

    protected function evaluateSlowMoving(InventoryRule $rule, ?int $warehouseId = null): array
    {
        $threshold = (float) ($rule->condition_config['velocity_threshold'] ?? 1.0);
        $days = (int) ($rule->condition_config['days'] ?? 90);

        $balances = InventoryBalance::where('quantity', '>', 0)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->get();

        $slowItems = $balances->filter(function ($balance) use ($threshold, $days) {
            $velocity = $this->reorderEngine->calculateSalesVelocity(
                productId: $balance->product_id,
                warehouseId: $balance->warehouse_id,
                days: $days,
            );

            return $velocity > 0 && $velocity < $threshold;
        });

        return $this->processItems($rule, $slowItems, 'slow_moving', function ($item) use ($threshold) {
            return [
                'title' => "Slow Moving: {$item->product?->name}",
                'message' => "Sales velocity is below {$threshold} units/day.",
                'severity' => AlertSeverityEnum::Info,
                'product_id' => $item->product_id,
                'warehouse_id' => $item->warehouse_id,
            ];
        });
    }

    protected function evaluateFastMoving(InventoryRule $rule, ?int $warehouseId = null): array
    {
        $threshold = (float) ($rule->condition_config['velocity_threshold'] ?? 10.0);
        $days = (int) ($rule->condition_config['days'] ?? 30);

        $balances = InventoryBalance::where('quantity', '>', 0)
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->get();

        $fastItems = $balances->filter(function ($balance) use ($threshold, $days) {
            $velocity = $this->reorderEngine->calculateSalesVelocity(
                productId: $balance->product_id,
                warehouseId: $balance->warehouse_id,
                days: $days,
            );

            return $velocity >= $threshold;
        });

        return $this->processItems($rule, $fastItems, 'fast_moving', function ($item) use ($threshold) {
            return [
                'title' => "Fast Moving: {$item->product?->name}",
                'message' => "Sales velocity is {$threshold}+ units/day. Consider increasing stock.",
                'severity' => AlertSeverityEnum::Info,
                'product_id' => $item->product_id,
                'warehouse_id' => $item->warehouse_id,
            ];
        });
    }

    protected function processItems(
        InventoryRule $rule,
        Collection $items,
        string $type,
        callable $formatAlert,
    ): array {
        $triggered = 0;

        foreach ($items as $item) {
            $item->loadMissing(['product:id,name,sku', 'warehouse:id,name']);

            if ($rule->action_type === 'create_alert') {
                $alertData = $formatAlert($item);

                $exists = InventoryAlert::where('type', $type)
                    ->where('product_id', $alertData['product_id'])
                    ->where('warehouse_id', $alertData['warehouse_id'])
                    ->whereNull('dismissed_at')
                    ->whereNull('resolved_at')
                    ->exists();

                if (! $exists) {
                    InventoryAlert::create([
                        'rule_id' => $rule->id,
                        'type' => $type,
                        'title' => $alertData['title'],
                        'message' => $alertData['message'],
                        'severity' => $alertData['severity']->value,
                        'product_id' => $alertData['product_id'],
                        'warehouse_id' => $alertData['warehouse_id'],
                    ]);

                    $triggered++;
                }
            }

            if ($rule->action_type === 'generate_suggestion' && $type === 'low_stock') {
                $this->reorderEngine->generateSuggestions($item->warehouse_id);
                $triggered++;
            }
        }

        return ['triggered' => $triggered, 'errors' => []];
    }
}
