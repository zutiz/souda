<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\DTOs\StockMovementDTO;
use App\Modules\Product\Enums\AuditActionEnum;
use App\Modules\Product\Events\LowStockAlert;
use App\Modules\Product\Events\StockDepleted;
use App\Modules\Product\Events\StockUpdated;
use App\Modules\Product\Models\AuditLog;
use App\Modules\Product\Models\StockMovement;
use App\Modules\Product\Models\WarehouseStock;
use Illuminate\Contracts\Events\Dispatcher;

class StockAuditService
{
    public function __construct(
        protected Dispatcher $events,
    ) {}

    public function recordAuditLog(StockMovement $movement, AuditActionEnum $action, array $oldValues, array $newValues): void
    {
        $changedFields = [];

        foreach ($newValues as $key => $value) {
            if (isset($oldValues[$key]) && $oldValues[$key] !== $value) {
                $changedFields[] = $key;
            }
        }

        AuditLog::query()->create([
            'tenant_id' => tenant()->id ?? 'unknown',
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name,
            'entity_type' => 'warehouse_stock',
            'entity_id' => $movement->id,
            'action' => $action->value,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => $changedFields,
            'reference_type' => $movement->reference_type,
            'reference_id' => (string) ($movement->reference_id ?? ''),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function checkStockThresholds(WarehouseStock $stock, ?string $productId, ?string $variantId): void
    {
        $available = $stock->quantity - $stock->reserved_quantity;

        if ($available <= 0) {
            $this->events->dispatch(new StockDepleted(
                productId: $productId ?? $variantId ?? 'unknown',
                variantId: $variantId,
                warehouseId: $stock->warehouse_id,
            ));
        } elseif ($available <= $stock->reorder_level) {
            $this->events->dispatch(new LowStockAlert(
                productId: $productId ?? $variantId ?? 'unknown',
                variantId: $variantId,
                warehouseId: $stock->warehouse_id,
                availableQuantity: $available,
                threshold: $stock->reorder_level,
            ));
        }
    }

    public function dispatchStockUpdated(StockMovement $movement, int $previousAvailable, int $newAvailable): void
    {
        $this->events->dispatch(new StockUpdated(
            movement: StockMovementDTO::fromModel($movement),
            previousAvailable: $previousAvailable,
            newAvailable: $newAvailable,
            snapshotBefore: $movement->snapshot_before,
            snapshotAfter: $movement->snapshot_after,
        ));
    }
}
