<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\BatchStatusEnum;
use App\Modules\Inventory\Events\BatchDepleted;
use App\Modules\Inventory\Events\BatchQuarantined;
use App\Modules\Inventory\Exceptions\BatchNotFoundException;
use App\Modules\Inventory\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Models\InventoryBatch;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BatchService
{
    public function receive(
        string $productId,
        int $warehouseId,
        string $batchNumber,
        int $quantity,
        ?string $variantId = null,
        ?string $supplierBatch = null,
        ?CarbonInterface $manufacturingDate = null,
        ?CarbonInterface $expiryDate = null,
        ?CarbonInterface $bestBefore = null,
        int $unitCost = 0,
    ): InventoryBatch {
        return DB::transaction(function () use (
            $productId, $variantId, $warehouseId, $batchNumber, $quantity,
            $supplierBatch, $manufacturingDate, $expiryDate, $bestBefore, $unitCost
        ) {
            $existing = InventoryBatch::where([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'batch_number' => $batchNumber,
            ])->whereIn('status', ['active', 'depleted'])->first();

            if ($existing && $existing->status === BatchStatusEnum::Active) {
                $existing->increment('remaining_quantity', $quantity);
                $existing->increment('initial_quantity', $quantity);

                return $existing->fresh();
            }

            return InventoryBatch::create([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'warehouse_id' => $warehouseId,
                'batch_number' => $batchNumber,
                'supplier_batch' => $supplierBatch,
                'manufacturing_date' => $manufacturingDate,
                'expiry_date' => $expiryDate,
                'best_before' => $bestBefore,
                'initial_quantity' => $quantity,
                'remaining_quantity' => $quantity,
                'unit_cost' => $unitCost,
                'status' => BatchStatusEnum::Active,
            ]);
        });
    }

    public function deduct(
        string $productId,
        int $warehouseId,
        string $batchNumber,
        int $quantity,
        ?string $variantId = null,
    ): InventoryBatch {
        return DB::transaction(function () use (
            $productId, $variantId, $warehouseId, $batchNumber, $quantity
        ) {
            $batch = InventoryBatch::where([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'batch_number' => $batchNumber,
                'status' => 'active',
            ])->lockForUpdate()->first();

            if ($batch === null) {
                throw new BatchNotFoundException($batchNumber, $productId);
            }

            if ($batch->remaining_quantity < $quantity) {
                throw InsufficientStockException::forProduct(
                    productId: $productId,
                    requested: $quantity,
                    available: $batch->remaining_quantity,
                );
            }

            $batch->decrement('remaining_quantity', $quantity);

            if ($batch->remaining_quantity <= 0) {
                $batch->markAsDepleted();

                event(new BatchDepleted(
                    batchId: $batch->id,
                    productId: $productId,
                    batchNumber: $batchNumber,
                ));
            }

            return $batch->fresh();
        });
    }

    public function pickBatches(
        string $productId,
        int $warehouseId,
        int $quantity,
        ?string $variantId = null,
        string $method = 'fefo',
    ): Collection {
        $query = InventoryBatch::where([
            'warehouse_id' => $warehouseId,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'status' => 'active',
        ])->where('remaining_quantity', '>', 0);

        switch ($method) {
            case 'fefo':
                $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '>=', now())
                    ->orderBy('expiry_date');
                break;
            case 'fifo':
                $query->orderBy('manufacturing_date')
                    ->orderBy('id');
                break;
            default:
                $query->orderByDesc('id');
                break;
        }

        $batches = $query->get();

        $totalAvailable = $batches->sum('remaining_quantity');
        if ($totalAvailable < $quantity) {
            throw InsufficientStockException::forProduct(
                productId: $productId,
                requested: $quantity,
                available: $totalAvailable,
            );
        }

        return $this->allocateFromBatches($batches, $quantity);
    }

    public function allocateFromBatches(Collection $batches, int $quantity): Collection
    {
        $allocated = new Collection;
        $remaining = $quantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $take = min($remaining, $batch->remaining_quantity);

            $virtualBatch = clone $batch;
            $virtualBatch->setRelation('pivot', new class($take)
            {
                public int $allocatedQuantity;

                public function __construct(int $allocatedQuantity)
                {
                    $this->allocatedQuantity = $allocatedQuantity;
                }
            });

            $allocated->push($virtualBatch);

            $remaining -= $take;
        }

        return $allocated;
    }

    public function quarantine(int $batchId, ?string $reason = null): InventoryBatch
    {
        return DB::transaction(function () use ($batchId, $reason) {
            $batch = InventoryBatch::lockForUpdate()->findOrFail($batchId);

            $batch->markAsQuarantined();

            event(new BatchQuarantined(
                batchId: $batch->id,
                productId: $batch->product_id,
                batchNumber: $batch->batch_number,
                reason: $reason,
            ));

            return $batch->fresh();
        });
    }

    public function findExpiring(int $withinDays = 30): Collection
    {
        return InventoryBatch::expiring($withinDays)->get();
    }

    public function findExpired(): Collection
    {
        return InventoryBatch::expired()->get();
    }

    public function expireBatches(): int
    {
        $count = 0;

        InventoryBatch::expired()->where('status', 'active')
            ->chunkById(100, function (Collection $batches) use (&$count) {
                foreach ($batches as $batch) {
                    $batch->markAsExpired();
                    $count++;
                }
            });

        return $count;
    }
}
