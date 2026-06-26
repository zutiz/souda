<?php

declare(strict_types=1);

namespace App\Modules\Product\Services;

use App\Modules\Product\DTOs\AllocationResult;
use App\Modules\Product\Enums\AuditActionEnum;
use App\Modules\Product\Enums\MovementTypeEnum;
use App\Modules\Product\Events\StockTransferCompleted;
use App\Modules\Product\Exceptions\InsufficientStockException;
use App\Modules\Product\Models\StockMovement;
use App\Modules\Product\Models\WarehouseStock;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function __construct(
        protected Dispatcher $events,
        protected StockReservationService $reservations,
        protected StockLockService $lockService,
        protected StockAuditService $auditService,
    ) {}

    public function receiveStock(int $warehouseId, ?string $productId, ?string $variantId, int $quantity, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($warehouseId, $productId, $variantId, $quantity, $notes) {
            $stock = $this->getOrCreateStock($warehouseId, $productId, $variantId);
            $snapshotBefore = $stock->toArray();

            $stock->increment('quantity', $quantity);
            $stock->update(['last_movement_at' => now()]);
            $stock->refresh();

            $movement = $this->recordMovement(
                warehouseId: $warehouseId,
                productId: $productId,
                variantId: $variantId,
                movementType: MovementTypeEnum::Received,
                quantity: $quantity,
                notes: $notes,
                snapshotBefore: $snapshotBefore,
                snapshotAfter: $stock->toArray(),
            );

            $this->auditService->recordAuditLog($movement, AuditActionEnum::StockReceived, $snapshotBefore, $stock->toArray());

            $this->auditService->dispatchStockUpdated($movement, $snapshotBefore['quantity'] ?? 0, $stock->quantity);

            return $movement;
        });
    }

    public function deductStock(int $warehouseId, ?string $productId, ?string $variantId, int $quantity, ?string $referenceType = null, ?int $referenceId = null, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($warehouseId, $productId, $variantId, $quantity, $referenceType, $referenceId, $notes) {
            $stock = $this->lockService->lockStockRecord($warehouseId, $productId, $variantId);
            $snapshotBefore = $stock->toArray();

            $available = $stock->quantity - $stock->reserved_quantity;

            if ($available < $quantity) {
                throw InsufficientStockException::forProduct(
                    productId: $productId ?? $variantId ?? 'unknown',
                    requested: $quantity,
                    available: $available,
                );
            }

            $stock->decrement('quantity', $quantity);
            $stock->update(['last_movement_at' => now()]);
            $stock->refresh();

            $movement = $this->recordMovement(
                warehouseId: $warehouseId,
                productId: $productId,
                variantId: $variantId,
                movementType: MovementTypeEnum::Sold,
                quantity: -$quantity,
                referenceType: $referenceType,
                referenceId: $referenceId,
                notes: $notes,
                snapshotBefore: $snapshotBefore,
                snapshotAfter: $stock->toArray(),
            );

            $this->auditService->recordAuditLog($movement, AuditActionEnum::StockDeducted, $snapshotBefore, $stock->toArray());

            $this->auditService->checkStockThresholds($stock, $productId, $variantId);

            $this->auditService->dispatchStockUpdated($movement, $snapshotBefore['quantity'] ?? 0, $stock->quantity);

            return $movement;
        });
    }

    public function adjustStock(int $warehouseId, ?string $productId, ?string $variantId, int $newQuantity, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($warehouseId, $productId, $variantId, $newQuantity, $notes) {
            $stock = $this->lockService->lockStockRecord($warehouseId, $productId, $variantId);
            $snapshotBefore = $stock->toArray();

            $difference = $newQuantity - $stock->quantity;

            $stock->update([
                'quantity' => $newQuantity,
                'last_movement_at' => now(),
            ]);
            $stock->refresh();

            $movement = $this->recordMovement(
                warehouseId: $warehouseId,
                productId: $productId,
                variantId: $variantId,
                movementType: MovementTypeEnum::Adjustment,
                quantity: $difference,
                notes: $notes,
                snapshotBefore: $snapshotBefore,
                snapshotAfter: $stock->toArray(),
            );

            $this->auditService->recordAuditLog($movement, AuditActionEnum::StockAdjusted, $snapshotBefore, $stock->toArray());

            $this->auditService->dispatchStockUpdated($movement, $snapshotBefore['quantity'] ?? 0, $stock->quantity);

            return $movement;
        });
    }

    public function transferStock(int $fromWarehouseId, int $toWarehouseId, ?string $productId, ?string $variantId, int $quantity, ?string $notes = null): array
    {
        return DB::transaction(function () use ($fromWarehouseId, $toWarehouseId, $productId, $variantId, $quantity, $notes) {
            $lockedRecords = $this->lockService->lockStockRecords(
                warehouseIds: [$fromWarehouseId, $toWarehouseId],
                productId: $productId,
                variantId: $variantId,
            );

            $fromStock = $lockedRecords[$fromWarehouseId];
            $toStock = $lockedRecords[$toWarehouseId];

            if (($fromStock->quantity - $fromStock->reserved_quantity) < $quantity) {
                throw InsufficientStockException::forProduct(
                    productId: $productId ?? $variantId ?? 'unknown',
                    requested: $quantity,
                    available: $fromStock->quantity - $fromStock->reserved_quantity,
                );
            }

            $fromSnapshot = $fromStock->toArray();
            $toSnapshot = $toStock->toArray();

            $fromStock->decrement('quantity', $quantity);
            $fromStock->update(['last_movement_at' => now()]);

            $toStock->increment('quantity', $quantity);
            $toStock->update(['last_movement_at' => now()]);

            $fromStock->refresh();
            $toStock->refresh();

            $outMovement = $this->recordMovement(
                warehouseId: $fromWarehouseId,
                productId: $productId,
                variantId: $variantId,
                movementType: MovementTypeEnum::TransferOut,
                quantity: -$quantity,
                notes: $notes,
                snapshotBefore: $fromSnapshot,
                snapshotAfter: $fromStock->toArray(),
            );

            $inMovement = $this->recordMovement(
                warehouseId: $toWarehouseId,
                productId: $productId,
                variantId: $variantId,
                movementType: MovementTypeEnum::TransferIn,
                quantity: $quantity,
                notes: $notes,
                snapshotBefore: $toSnapshot,
                snapshotAfter: $toStock->toArray(),
            );

            $this->auditService->recordAuditLog($outMovement, AuditActionEnum::StockTransferred, $fromSnapshot, $fromStock->toArray());

            $this->events->dispatch(new StockTransferCompleted(
                productId: $productId ?? $variantId ?? 'unknown',
                variantId: $variantId,
                fromWarehouseId: $fromWarehouseId,
                toWarehouseId: $toWarehouseId,
                quantity: $quantity,
            ));

            return [$outMovement, $inMovement];
        });
    }

    public function recordDamaged(int $warehouseId, ?string $productId, ?string $variantId, int $quantity, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($warehouseId, $productId, $variantId, $quantity, $notes) {
            $stock = $this->lockService->lockStockRecord($warehouseId, $productId, $variantId);
            $snapshotBefore = $stock->toArray();

            if ($stock->quantity < $quantity) {
                throw InsufficientStockException::forProduct(
                    productId: $productId ?? $variantId ?? 'unknown',
                    requested: $quantity,
                    available: $stock->quantity,
                );
            }

            $stock->decrement('quantity', $quantity);
            $stock->update(['last_movement_at' => now()]);
            $stock->refresh();

            $movement = $this->recordMovement(
                warehouseId: $warehouseId,
                productId: $productId,
                variantId: $variantId,
                movementType: MovementTypeEnum::Damaged,
                quantity: -$quantity,
                notes: $notes,
                snapshotBefore: $snapshotBefore,
                snapshotAfter: $stock->toArray(),
            );

            $this->auditService->recordAuditLog($movement, AuditActionEnum::StockDamaged, $snapshotBefore, $stock->toArray());

            return $movement;
        });
    }

    public function recordExpired(int $warehouseId, ?string $productId, ?string $variantId, int $quantity, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($warehouseId, $productId, $variantId, $quantity, $notes) {
            $stock = $this->lockService->lockStockRecord($warehouseId, $productId, $variantId);
            $snapshotBefore = $stock->toArray();

            if ($stock->quantity < $quantity) {
                throw InsufficientStockException::forProduct(
                    productId: $productId ?? $variantId ?? 'unknown',
                    requested: $quantity,
                    available: $stock->quantity,
                );
            }

            $stock->decrement('quantity', $quantity);
            $stock->update(['last_movement_at' => now()]);
            $stock->refresh();

            $movement = $this->recordMovement(
                warehouseId: $warehouseId,
                productId: $productId,
                variantId: $variantId,
                movementType: MovementTypeEnum::Expired,
                quantity: -$quantity,
                notes: $notes,
                snapshotBefore: $snapshotBefore,
                snapshotAfter: $stock->toArray(),
            );

            $this->auditService->recordAuditLog($movement, AuditActionEnum::StockExpired, $snapshotBefore, $stock->toArray());

            return $movement;
        });
    }

    public function getMovementHistory(?string $productId = null, ?string $variantId = null, ?int $warehouseId = null): LengthAwarePaginator
    {
        $query = StockMovement::query()->with(['warehouse']);

        if ($productId !== null) {
            $query->where('product_id', $productId);
        }

        if ($variantId !== null) {
            $query->where('variant_id', $variantId);
        }

        if ($warehouseId !== null) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->latest()->paginate(25);
    }

    public function allocateForOrder(array $lineItems): AllocationResult
    {
        return DB::transaction(function () use ($lineItems) {
            $allocations = [];

            foreach ($lineItems as $item) {
                $stock = $this->lockService->lockStockRecord(
                    $item['warehouse_id'],
                    $item['product_id'] ?? null,
                    $item['variant_id'] ?? null,
                );

                $available = $stock->quantity - $stock->reserved_quantity;

                if ($available < $item['quantity']) {
                    throw InsufficientStockException::forProduct(
                        productId: $item['product_id'] ?? $item['variant_id'] ?? 'unknown',
                        requested: $item['quantity'],
                        available: $available,
                    );
                }

                $snapshotBefore = $stock->toArray();

                $stock->decrement('quantity', $item['quantity']);
                $stock->update(['last_movement_at' => now()]);
                $stock->refresh();

                $movement = $this->recordMovement(
                    warehouseId: $item['warehouse_id'],
                    productId: $item['product_id'] ?? null,
                    variantId: $item['variant_id'] ?? null,
                    movementType: MovementTypeEnum::Sold,
                    quantity: -$item['quantity'],
                    referenceType: 'order',
                    referenceId: $item['order_id'] ?? null,
                    snapshotBefore: $snapshotBefore,
                    snapshotAfter: $stock->toArray(),
                );

                $this->auditService->recordAuditLog($movement, AuditActionEnum::StockDeducted, $snapshotBefore, $stock->toArray());

                $this->auditService->checkStockThresholds($stock, $item['product_id'] ?? null, $item['variant_id'] ?? null);

                $this->auditService->dispatchStockUpdated($movement, $snapshotBefore['quantity'] ?? 0, $stock->quantity);

                $allocations[] = [
                    'line_item' => $item,
                    'movement_id' => $movement->id,
                ];
            }

            return AllocationResult::success($allocations);
        });
    }

    protected function getOrCreateStock(int $warehouseId, ?string $productId, ?string $variantId): WarehouseStock
    {
        $stock = WarehouseStock::query()
            ->where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($stock === null) {
            $stock = WarehouseStock::query()->create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => 0,
                'reserved_quantity' => 0,
                'reorder_level' => 5,
            ]);
        }

        return $stock;
    }

    protected function recordMovement(
        int $warehouseId,
        ?string $productId,
        ?string $variantId,
        MovementTypeEnum $movementType,
        int $quantity,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        ?array $snapshotBefore = null,
        ?array $snapshotAfter = null,
    ): StockMovement {
        return StockMovement::query()->create([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'movement_type' => $movementType->value,
            'quantity' => $quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
            'performed_by' => auth()->id(),
            'snapshot_before' => $snapshotBefore,
            'snapshot_after' => $snapshotAfter,
        ]);
    }
}
