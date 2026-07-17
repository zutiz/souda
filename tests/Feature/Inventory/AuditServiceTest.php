<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\Audit\AuditService;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Inventory\Services\TransferEngine;
use App\Modules\Product\Models\AuditLog;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();

    tenancy()->initialize($this->user->tenant);

    $this->product = Product::factory()->create();
    $this->fromWarehouse = Warehouse::factory()->create(['slug' => 'audit-from']);
    $this->toWarehouse = Warehouse::factory()->create(['slug' => 'audit-to']);

    $this->inventoryEngine = app(InventoryEngine::class);
    $this->transferEngine = app(TransferEngine::class);
    $this->auditService = app(AuditService::class);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->fromWarehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-AUDIT',
    );
});

test('audit service records movement audit', function () {
    $ledger = $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->fromWarehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-AUDIT',
    );

    $this->auditService->recordMovement(
        ledger: $ledger,
        oldValues: ['quantity' => 0],
        newValues: ['quantity' => 100],
    );

    $log = AuditLog::where('entity_type', 'inventory_ledger')->first();

    expect($log)->not->toBeNull()
        ->and($log->action)->toBe('stock_initial_stock')
        ->and($log->entity_id)->toBe($ledger->id);
});

test('audit service records transfer audit', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 10],
        ],
    );

    $this->auditService->recordTransfer(
        transfer: $transfer,
        action: 'transfer_initiated',
        newValues: ['status' => 'draft'],
    );

    $log = AuditLog::where('entity_type', 'inventory_transfer')->first();

    expect($log)->not->toBeNull()
        ->and($log->action)->toBe('transfer_initiated')
        ->and($log->reference_id)->toBe($transfer->reference);
});

test('audit service records transfer cancellation audit on draft', function () {
    $transfer = $this->transferEngine->initiate(
        fromWarehouseId: $this->fromWarehouse->id,
        toWarehouseId: $this->toWarehouse->id,
        items: [
            ['product_id' => $this->product->id, 'variant_id' => null, 'quantity' => 10],
        ],
    );

    $this->transferEngine->cancel($transfer->id, 'Inventory audit');

    $log = AuditLog::where('entity_type', 'inventory_transfer')
        ->where('action', 'transfer_cancelled')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->old_values)->toEqual(['status' => 'draft'])
        ->and($log->new_values['status'])->toBe('cancelled')
        ->and($log->new_values['reason'])->toBe('Inventory audit');
});
