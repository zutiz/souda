<?php

declare(strict_types=1);

use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\SerialNumber;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\BatchService;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Inventory\Services\SerialNumberService;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->sharedSubscribed()->create();

    tenancy()->initialize($this->user->tenant);
    app(TenantManager::class)->initialize($this->user->tenant);

    $this->product = Product::factory()->create();
    $this->warehouse = Warehouse::factory()->create(['slug' => 'batch-int-wh']);
    $this->inventoryEngine = app(InventoryEngine::class);
    $this->batchService = app(BatchService::class);
    $this->serialService = app(SerialNumberService::class);
});

test('recordMovement links to batch via FK on inbound', function () {
    $batch = $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 100,
        unitCost: 500,
    );

    $ledger = $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-BAT-001',
        batchId: $batch->id,
    );

    expect($ledger->batch_id)->toBe($batch->id);
});

test('batch service and inventory engine work together for full lifecycle', function () {
    $batch = $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-002',
        quantity: 100,
        unitCost: 500,
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-BAT-002',
        batchId: $batch->id,
    );

    $balance = InventoryBalance::where([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
    ])->first();

    expect($balance->quantity)->toBe(100);

    $this->batchService->deduct(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-002',
        quantity: 20,
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: -20,
        movementType: 'sale_deduction',
        reference: 'SAL-001',
    );

    $balance->refresh();
    expect($balance->quantity)->toBe(80);

    $batch->refresh();
    expect($batch->remaining_quantity)->toBe(80);
});

test('serial number service works independently from inventory engine', function () {
    $this->serialService->registerBatch(
        productId: $this->product->id,
        serialNumbers: ['SN-100', 'SN-101', 'SN-102'],
        warehouseId: $this->warehouse->id,
    );

    expect(SerialNumber::where('product_id', $this->product->id)->count())->toBe(3);

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 3,
        movementType: 'purchase_receipt',
        reference: 'PUR-SN-001',
    );

    $balance = InventoryBalance::where([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
    ])->first();

    expect($balance->quantity)->toBe(3);
});

test('serial number lifecycle works end-to-end', function () {
    $this->serialService->registerBatch(
        productId: $this->product->id,
        serialNumbers: ['SN-200', 'SN-201'],
        warehouseId: $this->warehouse->id,
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 2,
        movementType: 'purchase_receipt',
        reference: 'PUR-SN-002',
    );

    $this->serialService->markAsSold(
        serialNumber: 'SN-200',
        productId: $this->product->id,
        orderReference: 'SAL-001',
    );

    $serial = SerialNumber::where('serial_number', 'SN-200')->first();
    expect($serial->status->value)->toBe('sold');

    $serialAvailable = SerialNumber::where('serial_number', 'SN-201')->first();
    expect($serialAvailable->status->value)->toBe('available');
});
