<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\CountItemStatusEnum;
use App\Modules\Inventory\Enums\CountStatusEnum;
use App\Modules\Inventory\Events\CountAdjustmentsApplied;
use App\Modules\Inventory\Events\CountCompleted;
use App\Modules\Inventory\Events\CountCreated;
use App\Modules\Inventory\Events\CountItemRecorded;
use App\Modules\Inventory\Events\CountVerified;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryCount;
use App\Modules\Inventory\Models\InventoryCountItem;
use App\Modules\Product\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CountEngine
{
    public function __construct(
        protected InventoryEngine $inventoryEngine,
    ) {}

    public function createCount(int $warehouseId, string $type, ?int $countedBy = null, ?array $productIds = null): InventoryCount
    {
        $reference = $this->generateReference();

        return DB::transaction(function () use ($warehouseId, $type, $reference, $countedBy, $productIds) {
            $count = InventoryCount::create([
                'warehouse_id' => $warehouseId,
                'reference' => $reference,
                'type' => $type,
                'status' => CountStatusEnum::Draft,
                'counted_by' => $countedBy,
            ]);

            $balances = InventoryBalance::where('warehouse_id', $warehouseId)
                ->where('quantity', '>', 0)
                ->when($productIds, fn ($q) => $q->whereIn('product_id', $productIds))
                ->get();

            $products = Product::whereIn('id', $balances->pluck('product_id'))
                ->get()
                ->keyBy('id');

            foreach ($balances as $balance) {
                $product = $products->get($balance->product_id);

                InventoryCountItem::create([
                    'count_id' => $count->id,
                    'product_id' => $balance->product_id,
                    'variant_id' => $balance->variant_id,
                    'expected_quantity' => $balance->quantity,
                    'unit_cost' => $balance->average_unit_cost,
                    'status' => CountItemStatusEnum::Pending,
                ]);
            }

            event(new CountCreated($count));

            return $count;
        });
    }

    public function recordCounts(InventoryCount $count, array $items): void
    {
        if ($count->status === CountStatusEnum::Draft) {
            $count->update(['status' => CountStatusEnum::InProgress]);
        }

        if ($count->status !== CountStatusEnum::InProgress) {
            throw new \InvalidArgumentException('Count must be in draft or in_progress status to record counts.');
        }

        foreach ($items as $item) {
            $countItem = InventoryCountItem::findOrFail($item['id']);

            if ($countItem->count_id !== $count->id) {
                throw new \InvalidArgumentException("Item {$countItem->id} does not belong to count {$count->id}.");
            }

            $physical = (int) ($item['physical_quantity'] ?? 0);
            $discrepancy = $physical - $countItem->expected_quantity;

            $countItem->update([
                'physical_quantity' => $physical,
                'discrepancy' => $discrepancy,
                'status' => CountItemStatusEnum::Counted,
                'notes' => $item['notes'] ?? $countItem->notes,
            ]);

            event(new CountItemRecorded($countItem));
        }

        $count->update(['counted_at' => now()]);
    }

    public function verifyCount(InventoryCount $count, int $verifiedBy): void
    {
        if ($count->status !== CountStatusEnum::InProgress) {
            throw new \InvalidArgumentException('Count must be in progress to verify.');
        }

        DB::transaction(function () use ($count, $verifiedBy) {
            $count->items()->where('status', CountItemStatusEnum::Counted)
                ->update(['status' => CountItemStatusEnum::Verified]);

            $count->update([
                'status' => CountStatusEnum::Verified,
                'verified_by' => $verifiedBy,
                'verified_at' => now(),
            ]);
        });

        event(new CountVerified($count));
    }

    public function applyAdjustments(InventoryCount $count): int
    {
        if ($count->status !== CountStatusEnum::Verified) {
            throw new \InvalidArgumentException('Count must be verified before applying adjustments.');
        }

        $adjusted = 0;

        DB::transaction(function () use ($count, &$adjusted) {
            $items = $count->items()
                ->where('status', CountItemStatusEnum::Verified)
                ->where(function ($q) {
                    $q->where('discrepancy', '!=', 0)->whereNotNull('discrepancy');
                })
                ->get();

            foreach ($items as $item) {
                $movementType = $item->discrepancy > 0
                    ? 'adjustment_increase'
                    : 'adjustment_decrease';

                $this->inventoryEngine->recordMovement(
                    productId: $item->product_id,
                    variantId: $item->variant_id,
                    warehouseId: $count->warehouse_id,
                    quantity: $item->discrepancy,
                    movementType: $movementType,
                    reference: $count->reference,
                    description: "Physical count adjustment (count #{$count->id})",
                );

                $adjusted++;
            }

            $count->update([
                'status' => CountStatusEnum::Adjusted,
                'adjusted_at' => now(),
            ]);
        });

        if ($adjusted > 0) {
            event(new CountAdjustmentsApplied($count, $adjusted));
        }

        return $adjusted;
    }

    public function completeCount(InventoryCount $count): void
    {
        if ($count->status !== CountStatusEnum::Adjusted && $count->status !== CountStatusEnum::Verified) {
            throw new \InvalidArgumentException('Count must be adjusted or verified before completing.');
        }

        $count->update([
            'status' => CountStatusEnum::Completed,
            'completed_at' => now(),
        ]);

        event(new CountCompleted($count));
    }

    public function cancelCount(InventoryCount $count): void
    {
        if ($count->status->isTerminal()) {
            throw new \InvalidArgumentException('Cannot cancel a count that is already completed or cancelled.');
        }

        $count->update(['status' => CountStatusEnum::Cancelled]);
    }

    public function getDiscrepanciesForWarehouse(int $warehouseId, ?string $status = null): Collection
    {
        $query = InventoryCount::where('warehouse_id', $warehouseId)
            ->whereHas('items', fn ($q) => $q->where('discrepancy', '!=', 0));

        if ($status !== null) {
            $query->where('status', $status);
        }

        return $query->with(['items' => fn ($q) => $q->where('discrepancy', '!=', 0)->with('product:id,name,sku')])
            ->get();
    }

    protected function generateReference(): string
    {
        $prefix = config('inventory.count_reference_prefix', 'CNT');
        $date = now()->format('Ymd');
        $last = InventoryCount::where('reference', 'like', "{$prefix}-{$date}-%")
            ->orderBy('id', 'desc')
            ->value('reference');

        $seq = $last ? (int) substr($last, strrpos($last, '-') + 1) + 1 : 1;

        return "{$prefix}-{$date}-".str_pad((string) $seq, 3, '0', STR_PAD_LEFT);
    }
}
