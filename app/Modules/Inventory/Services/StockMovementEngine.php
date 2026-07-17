<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\DTOs\StockMovementDTO;
use App\Modules\Inventory\Enums\MovementTypeEnum;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryLedger;
use Illuminate\Support\Collection;

class StockMovementEngine
{
    public function record(
        string $productId,
        ?string $variantId,
        int $warehouseId,
        int $quantity,
        MovementTypeEnum $type,
        string $reference,
        ?int $unitCost = null,
        ?int $batchId = null,
        ?array $serialNumbers = null,
        ?string $description = null,
        ?string $createdBy = null,
        array $metadata = [],
    ): InventoryLedger {
        $balance = InventoryBalance::where([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'warehouse_id' => $warehouseId,
        ])->first();

        $quantityBefore = $balance?->quantity ?? 0;
        $quantityAfter = $quantityBefore + $quantity;

        $totalCost = $unitCost !== null ? abs($quantity) * $unitCost : null;

        $ledger = InventoryLedger::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'warehouse_id' => $warehouseId,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => max(0, $quantityAfter),
            'movement_type' => $type->value,
            'reference' => $reference,
            'reference_type' => $type->value,
            'batch_id' => $batchId,
            'serial_numbers' => $serialNumbers,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'description' => $description,
            'metadata' => ! empty($metadata) ? $metadata : null,
            'created_by' => $createdBy,
            'created_at' => now(),
        ]);

        return $ledger;
    }

    public function recordFromDTO(StockMovementDTO $dto): InventoryLedger
    {
        return $this->record(
            productId: $dto->productId,
            variantId: $dto->variantId,
            warehouseId: $dto->warehouseId,
            quantity: $dto->quantity,
            type: $dto->type,
            reference: $dto->reference,
            unitCost: $dto->unitCost,
            batchId: $dto->batchId,
            serialNumbers: $dto->serialNumbers,
            description: $dto->description,
            createdBy: $dto->createdBy,
            metadata: $dto->metadata,
        );
    }

    public function generateReference(MovementTypeEnum $type): string
    {
        $prefix = $type->referencePrefix();
        $date = now()->format('Ymd');
        $lastSeq = InventoryLedger::where('reference', 'like', "{$prefix}-{$date}-%")
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->value('reference');

        $lastNumber = 0;
        if ($lastSeq) {
            $parts = explode('-', $lastSeq);
            $lastNumber = (int) end($parts);
        }

        $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$nextNumber}";
    }

    public function findById(int $id): ?InventoryLedger
    {
        return InventoryLedger::find($id);
    }

    public function findByReference(string $reference): Collection
    {
        return InventoryLedger::where('reference', $reference)
            ->orderBy('id')
            ->get();
    }

    public function findByProduct(
        string $productId,
        ?int $warehouseId = null,
        ?string $movementType = null,
        ?string $from = null,
        ?string $to = null,
        ?int $limit = null,
    ): Collection {
        $query = InventoryLedger::where('product_id', $productId);

        if ($warehouseId !== null) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($movementType !== null) {
            $query->where('movement_type', $movementType);
        }

        if ($from !== null) {
            $query->where('created_at', '>=', $from);
        }

        if ($to !== null) {
            $query->where('created_at', '<=', $to);
        }

        $query->orderBy('created_at', 'desc');

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
