<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\TransferStatusEnum;
use App\Modules\Inventory\Events\TransferCancelled;
use App\Modules\Inventory\Events\TransferCompleted;
use App\Modules\Inventory\Events\TransferInitiated;
use App\Modules\Inventory\Exceptions\InvalidTransferStateException;
use App\Modules\Inventory\Exceptions\TransferNotFoundException;
use App\Modules\Inventory\Models\InventoryTransfer;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class TransferEngine
{
    public function __construct(
        private InventoryEngine $inventoryEngine,
        private ReservationEngine $reservationEngine,
        private AuditService $auditService,
    ) {}

    public function initiate(
        int $fromWarehouseId,
        int $toWarehouseId,
        array $items,
        ?string $reference = null,
        ?string $description = null,
    ): InventoryTransfer {
        if ($fromWarehouseId === $toWarehouseId) {
            throw new \InvalidArgumentException('Source and destination warehouses must be different');
        }

        $fromWarehouse = Warehouse::find($fromWarehouseId);
        if ($fromWarehouse === null) {
            throw new \InvalidArgumentException("Source warehouse not found: {$fromWarehouseId}");
        }

        $toWarehouse = Warehouse::find($toWarehouseId);
        if ($toWarehouse === null) {
            throw new \InvalidArgumentException("Destination warehouse not found: {$toWarehouseId}");
        }

        if (empty($items)) {
            throw new \InvalidArgumentException('Transfer must contain at least one item');
        }

        $reference ??= $this->generateReference();

        return DB::transaction(function () use (
            $fromWarehouseId, $toWarehouseId, $items, $reference, $description
        ) {
            $transfer = InventoryTransfer::create([
                'reference' => $reference,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'status' => TransferStatusEnum::Draft,
                'description' => $description,
            ]);

            foreach ($items as $item) {
                $transferItem = $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'quantity_received' => 0,
                ]);

                $this->reservationEngine->reserve(
                    warehouseId: $fromWarehouseId,
                    productId: $item['product_id'],
                    variantId: $item['variant_id'] ?? null,
                    quantity: $item['quantity'],
                    reference: $reference,
                    referenceType: 'transfer',
                );
            }

            event(new TransferInitiated(
                transferId: $transfer->id,
                fromWarehouseId: $fromWarehouseId,
                toWarehouseId: $toWarehouseId,
                itemCount: count($items),
            ));

            return $transfer;
        });
    }

    public function send(int $transferId): InventoryTransfer
    {
        return DB::transaction(function () use ($transferId) {
            $transfer = InventoryTransfer::lockForUpdate()->find($transferId);

            if ($transfer === null) {
                throw new TransferNotFoundException($transferId);
            }

            if ($transfer->status !== TransferStatusEnum::Draft) {
                throw new InvalidTransferStateException(
                    'Only draft transfers can be sent',
                );
            }

            $items = $transfer->items;

            foreach ($items as $item) {
                $reservation = $this->reservationEngine->getActiveReservations(
                    productId: $item->product_id,
                    warehouseId: $transfer->from_warehouse_id,
                    variantId: $item->variant_id,
                )->first();

                if ($reservation !== null) {
                    $this->reservationEngine->consume($reservation->id);
                }

                $this->inventoryEngine->recordMovement(
                    productId: $item->product_id,
                    variantId: $item->variant_id,
                    warehouseId: $transfer->from_warehouse_id,
                    quantity: -$item->quantity,
                    movementType: 'transfer_out',
                    reference: $transfer->reference,
                    description: "Transfer to warehouse {$transfer->to_warehouse_id}: {$transfer->reference}",
                );
            }

            $transfer->update([
                'status' => TransferStatusEnum::InTransit,
                'sent_at' => now(),
            ]);

            return $transfer;
        });
    }

    public function receive(int $transferId): InventoryTransfer
    {
        return DB::transaction(function () use ($transferId) {
            $transfer = InventoryTransfer::lockForUpdate()->find($transferId);

            if ($transfer === null) {
                throw new TransferNotFoundException($transferId);
            }

            if ($transfer->status !== TransferStatusEnum::InTransit) {
                throw new InvalidTransferStateException(
                    'Only in-transit transfers can be received',
                );
            }

            $items = $transfer->items;

            foreach ($items as $item) {
                $this->inventoryEngine->recordMovement(
                    productId: $item->product_id,
                    variantId: $item->variant_id,
                    warehouseId: $transfer->to_warehouse_id,
                    quantity: $item->quantity,
                    movementType: 'transfer_in',
                    reference: $transfer->reference,
                    description: "Transfer from warehouse {$transfer->from_warehouse_id}: {$transfer->reference}",
                );

                $item->update(['quantity_received' => $item->quantity]);
            }

            $transfer->update([
                'status' => TransferStatusEnum::Completed,
                'received_at' => now(),
            ]);

            event(new TransferCompleted(
                transferId: $transfer->id,
                fromWarehouseId: $transfer->from_warehouse_id,
                toWarehouseId: $transfer->to_warehouse_id,
                itemCount: $items->count(),
            ));

            return $transfer;
        });
    }

    public function cancel(int $transferId, ?string $reason = null): InventoryTransfer
    {
        return DB::transaction(function () use ($transferId, $reason) {
            $transfer = InventoryTransfer::lockForUpdate()->find($transferId);

            if ($transfer === null) {
                throw new TransferNotFoundException($transferId);
            }

            if ($transfer->status->isTerminal()) {
                throw new InvalidTransferStateException(
                    'Cannot cancel a transfer that is already completed or cancelled',
                );
            }

            $items = $transfer->items;

            $activeReservations = $this->reservationEngine->getActiveReservations(
                productId: '',
                warehouseId: $transfer->from_warehouse_id,
            );

            foreach ($items as $item) {
                $reservation = $activeReservations->first(
                    fn ($r) => $r->product_id === $item->product_id
                        && $r->variant_id === $item->variant_id
                        && $r->reference === $transfer->reference,
                );

                if ($reservation !== null) {
                    $this->reservationEngine->cancel($reservation->id);
                }
            }

            if ($transfer->status === TransferStatusEnum::InTransit) {
                foreach ($items as $item) {
                    $remainingQty = $item->quantity - $item->quantity_received;

                    if ($remainingQty > 0) {
                        $this->inventoryEngine->recordMovement(
                            productId: $item->product_id,
                            variantId: $item->variant_id,
                            warehouseId: $transfer->from_warehouse_id,
                            quantity: $remainingQty,
                            movementType: 'transfer_in',
                            reference: $transfer->reference.'-RETURN',
                            description: "Return from cancelled transfer: {$transfer->reference}",
                        );
                    }
                }
            }

            if ($transfer->status === TransferStatusEnum::Draft) {
                $this->auditService->recordTransferCancellation($transfer, $reason);
            }

            $transfer->update([
                'status' => TransferStatusEnum::Cancelled,
                'cancelled_at' => now(),
            ]);

            event(new TransferCancelled(
                transferId: $transfer->id,
                reason: $reason,
            ));

            return $transfer;
        });
    }

    public function partialReceive(int $transferId, array $receivedItems): InventoryTransfer
    {
        return DB::transaction(function () use ($transferId, $receivedItems) {
            $transfer = InventoryTransfer::lockForUpdate()->find($transferId);

            if ($transfer === null) {
                throw new TransferNotFoundException($transferId);
            }

            if ($transfer->status !== TransferStatusEnum::InTransit) {
                throw new InvalidTransferStateException(
                    'Only in-transit transfers can receive partial quantities',
                );
            }

            $allReceived = true;

            foreach ($transfer->items as $item) {
                $receivedQty = $receivedItems[$item->id] ?? null;

                if ($receivedQty === null) {
                    $allReceived = false;

                    continue;
                }

                $remainingToReceive = $item->quantity - $item->quantity_received;

                if ($receivedQty > $remainingToReceive) {
                    throw new \InvalidArgumentException(
                        "Received quantity {$receivedQty} exceeds remaining quantity {$remainingToReceive} for item {$item->id}",
                    );
                }

                if ($receivedQty > 0) {
                    $this->inventoryEngine->recordMovement(
                        productId: $item->product_id,
                        variantId: $item->variant_id,
                        warehouseId: $transfer->to_warehouse_id,
                        quantity: $receivedQty,
                        movementType: 'transfer_in',
                        reference: $transfer->reference,
                        description: "Partial transfer receive from warehouse {$transfer->from_warehouse_id}: {$transfer->reference}",
                    );

                    $item->update([
                        'quantity_received' => $item->quantity_received + $receivedQty,
                    ]);
                }
            }

            if ($allReceived) {
                $transfer->update([
                    'status' => TransferStatusEnum::Completed,
                    'received_at' => now(),
                ]);

                event(new TransferCompleted(
                    transferId: $transfer->id,
                    fromWarehouseId: $transfer->from_warehouse_id,
                    toWarehouseId: $transfer->to_warehouse_id,
                    itemCount: $transfer->items->count(),
                ));
            }

            return $transfer;
        });
    }

    private function generateReference(): string
    {
        $prefix = 'TRF';
        $date = now()->format('Ymd');
        $lastSeq = InventoryTransfer::where('reference', 'like', "{$prefix}-{$date}-%")
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
}
