<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services\Audit;

use App\Modules\Inventory\Models\InventoryLedger;
use App\Modules\Inventory\Models\InventoryTransfer;
use App\Modules\Inventory\Models\StockReservation;
use App\Modules\Product\Models\AuditLog;

class AuditService
{
    private function tenantId(): ?string
    {
        if (function_exists('tenant') && tenant() !== null) {
            return tenant()->id;
        }

        return null;
    }

    public function recordMovement(InventoryLedger $ledger, ?array $oldValues = null, ?array $newValues = null): void
    {
        AuditLog::create([
            'tenant_id' => $this->tenantId(),
            'entity_type' => 'inventory_ledger',
            'entity_id' => (string) $ledger->id,
            'action' => "stock_{$ledger->movement_type->value}",
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'reference_type' => $ledger->reference_type,
            'reference_id' => $ledger->reference,
        ]);
    }

    public function recordTransfer(InventoryTransfer $transfer, string $action, ?array $oldValues = null, ?array $newValues = null): void
    {
        AuditLog::create([
            'tenant_id' => $this->tenantId(),
            'entity_type' => 'inventory_transfer',
            'entity_id' => (string) $transfer->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'reference_type' => 'transfer',
            'reference_id' => $transfer->reference,
        ]);
    }

    public function recordTransferCancellation(InventoryTransfer $transfer, ?string $reason = null): void
    {
        AuditLog::create([
            'tenant_id' => $this->tenantId(),
            'entity_type' => 'inventory_transfer',
            'entity_id' => (string) $transfer->id,
            'action' => 'transfer_cancelled',
            'old_values' => ['status' => $transfer->status->value],
            'new_values' => ['status' => 'cancelled', 'reason' => $reason],
            'reference_type' => 'transfer',
            'reference_id' => $transfer->reference,
        ]);
    }

    public function recordReservation(StockReservation $reservation, string $action, ?array $oldValues = null, ?array $newValues = null): void
    {
        AuditLog::create([
            'tenant_id' => $this->tenantId(),
            'entity_type' => 'inventory_reservation',
            'entity_id' => (string) $reservation->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'reference_type' => $reservation->reference_type,
            'reference_id' => $reservation->reference,
        ]);
    }
}
