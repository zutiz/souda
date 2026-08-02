<?php

declare(strict_types=1);

use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Enums\BatchStatusEnum;
use App\Modules\Inventory\Events\BatchDepleted;
use App\Modules\Inventory\Events\BatchQuarantined;
use App\Modules\Inventory\Exceptions\BatchNotFoundException;
use App\Modules\Inventory\Exceptions\InsufficientStockException;
use App\Modules\Inventory\Models\InventoryBatch;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\BatchService;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->sharedSubscribed()->create();

    tenancy()->initialize($this->user->tenant);
    app(TenantManager::class)->initialize($this->user->tenant);

    $this->product = Product::factory()->create();
    $this->warehouse = Warehouse::factory()->create(['slug' => 'batch-wh']);
    $this->batchService = app(BatchService::class);
});

test('can receive a new batch', function () {
    $batch = $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 100,
        unitCost: 500,
    );

    expect($batch)->toBeInstanceOf(InventoryBatch::class)
        ->and($batch->batch_number)->toBe('BAT-001')
        ->and($batch->initial_quantity)->toBe(100)
        ->and($batch->remaining_quantity)->toBe(100)
        ->and($batch->unit_cost)->toBe(500)
        ->and($batch->status)->toBe(BatchStatusEnum::Active);
});

test('receive increments existing active batch', function () {
    $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 100,
    );

    $batch = $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 50,
    );

    expect($batch->initial_quantity)->toBe(150)
        ->and($batch->remaining_quantity)->toBe(150);
});

test('can deduct from a batch', function () {
    $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 100,
    );

    $batch = $this->batchService->deduct(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 30,
    );

    expect($batch->remaining_quantity)->toBe(70);
});

test('deduct throws on non-existent batch', function () {
    expect(fn () => $this->batchService->deduct(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'NONEXISTENT',
        quantity: 10,
    ))->toThrow(BatchNotFoundException::class);
});

test('deduct throws on insufficient batch quantity', function () {
    $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 10,
    );

    expect(fn () => $this->batchService->deduct(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 20,
    ))->toThrow(InsufficientStockException::class);
});

test('deduct marks batch depleted when quantity reaches zero', function () {
    $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 10,
    );

    $batch = $this->batchService->deduct(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 10,
    );

    expect($batch->remaining_quantity)->toBe(0)
        ->and($batch->status)->toBe(BatchStatusEnum::Depleted);
});

test('deduct dispatches BatchDepleted event when depleted', function () {
    Event::fake([BatchDepleted::class]);

    $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 5,
    );

    $this->batchService->deduct(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 5,
    );

    Event::assertDispatched(BatchDepleted::class);
});

test('pickBatches uses FEFO by default', function () {
    $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 50,
        expiryDate: now()->addDays(30),
    );

    $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-002',
        quantity: 50,
        expiryDate: now()->addDays(10),
    );

    $batches = $this->batchService->pickBatches(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        quantity: 30,
        method: 'fefo',
    );

    expect($batches)->toHaveCount(1)
        ->and($batches->first()->batch_number)->toBe('BAT-002');
});

test('pickBatches allocates across multiple batches when needed', function () {
    $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 20,
        expiryDate: now()->addDays(30),
    );

    $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-002',
        quantity: 20,
        expiryDate: now()->addDays(10),
    );

    $batches = $this->batchService->pickBatches(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        quantity: 30,
        method: 'fefo',
    );

    expect($batches)->toHaveCount(2)
        ->and($batches->first()->batch_number)->toBe('BAT-002');
});

test('can quarantine a batch', function () {
    $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 100,
    );

    $batch = $this->batchService->quarantine(
        batchId: 1,
        reason: 'Quality check failed',
    );

    expect($batch->status)->toBe(BatchStatusEnum::Quarantined);
});

test('quarantine dispatches BatchQuarantined event', function () {
    Event::fake([BatchQuarantined::class]);

    $this->batchService->receive(
        productId: $this->product->id,
        warehouseId: $this->warehouse->id,
        batchNumber: 'BAT-001',
        quantity: 100,
    );

    $this->batchService->quarantine(batchId: 1, reason: 'test');

    Event::assertDispatched(BatchQuarantined::class);
});

test('findExpiring returns batches expiring within days', function () {
    InventoryBatch::factory()->expiring(5)->create([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
    ]);

    InventoryBatch::factory()->create([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
        'batch_number' => 'BAT-NOEXP',
    ]);

    $expiring = $this->batchService->findExpiring(withinDays: 30);

    expect($expiring)->toHaveCount(1);
});

test('expireBatches marks expired batches', function () {
    InventoryBatch::factory()->expired()->create([
        'product_id' => $this->product->id,
        'warehouse_id' => $this->warehouse->id,
        'batch_number' => 'BAT-EXP',
        'remaining_quantity' => 50,
    ]);

    $count = $this->batchService->expireBatches();

    expect($count)->toBe(1);

    $batch = InventoryBatch::where('batch_number', 'BAT-EXP')->first();
    expect($batch->status)->toBe(BatchStatusEnum::Expired);
});
