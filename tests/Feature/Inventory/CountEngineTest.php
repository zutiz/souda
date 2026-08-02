<?php

declare(strict_types=1);

use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Enums\CountItemStatusEnum;
use App\Modules\Inventory\Enums\CountStatusEnum;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\CountEngine;
use App\Modules\Inventory\Services\InventoryEngine;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->sharedSubscribed()->create();

    tenancy()->initialize($this->user->tenant);
    app(TenantManager::class)->initialize($this->user->tenant);

    $this->product = Product::factory()->create(['track_inventory' => true]);
    $this->productB = Product::factory()->create(['track_inventory' => true]);
    $this->warehouse = Warehouse::factory()->create();

    $this->inventoryEngine = app(InventoryEngine::class);
    $this->countEngine = app(CountEngine::class);

    // Seed stock
    $this->inventoryEngine->recordMovement(
        productId: $this->product->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 100,
        movementType: 'initial_stock',
        reference: 'INIT-COUNT-A',
    );

    $this->inventoryEngine->recordMovement(
        productId: $this->productB->id,
        variantId: null,
        warehouseId: $this->warehouse->id,
        quantity: 50,
        movementType: 'initial_stock',
        reference: 'INIT-COUNT-B',
    );
});

test('createCount creates draft count with items from warehouse balances', function () {
    $count = $this->countEngine->createCount(
        warehouseId: $this->warehouse->id,
        type: 'full',
    );

    expect($count)
        ->status->toBe(CountStatusEnum::Draft)
        ->type->toBe('full')
        ->reference->toStartWith('CNT-')
        ->warehouse_id->toBe($this->warehouse->id);

    expect($count->items)->toHaveCount(2);
    expect($count->items->pluck('expected_quantity')->sort()->values()->toArray())
        ->toBe([50, 100]);
});

test('createCount supports partial count with product filter', function () {
    $count = $this->countEngine->createCount(
        warehouseId: $this->warehouse->id,
        type: 'partial',
        productIds: [$this->product->id],
    );

    expect($count->items)->toHaveCount(1);
    expect($count->items->first()->product_id)->toBe($this->product->id);
});

test('recordCounts transitions draft to in_progress and stores physical quantities', function () {
    $count = $this->countEngine->createCount(
        warehouseId: $this->warehouse->id,
        type: 'full',
    );

    $items = $count->items->map(fn ($item) => [
        'id' => $item->id,
        'physical_quantity' => $item->expected_quantity, // match expected
    ])->toArray();

    $this->countEngine->recordCounts($count, $items);

    $count->refresh();

    expect($count->status)->toBe(CountStatusEnum::InProgress);
    expect($count->counted_at)->not->toBeNull();

    foreach ($count->items as $item) {
        expect($item->status)->toBe(CountItemStatusEnum::Counted);
        expect($item->discrepancy)->toBe(0);
    }
});

test('recordCounts captures discrepancies', function () {
    $count = $this->countEngine->createCount(
        warehouseId: $this->warehouse->id,
        type: 'full',
    );

    $item = $count->items->first();
    $diff = -3;

    $this->countEngine->recordCounts($count, [
        ['id' => $item->id, 'physical_quantity' => $item->expected_quantity + $diff],
    ]);

    $item->refresh();

    expect($item->discrepancy)->toBe($diff);
});

test('recordCounts throws for wrong count items', function () {
    $countA = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');
    $countB = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');

    $itemFromB = $countB->items->first();

    $this->countEngine->recordCounts($countA, [
        ['id' => $itemFromB->id, 'physical_quantity' => 10],
    ]);
})->throws(InvalidArgumentException::class);

test('recordCounts throws for cancelled count', function () {
    $count = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');
    $this->countEngine->cancelCount($count);

    $this->countEngine->recordCounts($count, $count->items->map(fn ($i) => [
        'id' => $i->id, 'physical_quantity' => 5,
    ])->toArray());
})->throws(InvalidArgumentException::class);

test('verifyCount transitions to verified', function () {
    $count = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');

    $this->countEngine->recordCounts($count, $count->items->map(fn ($i) => [
        'id' => $i->id, 'physical_quantity' => $i->expected_quantity,
    ])->toArray());

    $this->countEngine->verifyCount($count, $this->user->id);

    $count->refresh();

    expect($count->status)->toBe(CountStatusEnum::Verified);
    expect($count->verified_by)->toBe($this->user->id);
    expect($count->verified_at)->not->toBeNull();

    foreach ($count->items as $item) {
        expect($item->status)->toBe(CountItemStatusEnum::Verified);
    }
});

test('verifyCount throws for non-in-progress count', function () {
    $count = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');

    $this->countEngine->verifyCount($count, $this->user->id);
})->throws(InvalidArgumentException::class);

test('applyAdjustments creates stock movements for discrepancies', function () {
    $count = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');

    $item = $count->items->where('expected_quantity', 100)->first();
    $this->countEngine->recordCounts($count, [
        ['id' => $item->id, 'physical_quantity' => 103], // +3 surplus
    ]);

    $this->countEngine->verifyCount($count, $this->user->id);

    $adjusted = $this->countEngine->applyAdjustments($count);

    expect($adjusted)->toBe(1);

    $count->refresh();
    expect($count->status)->toBe(CountStatusEnum::Adjusted);
});

test('applyAdjustments skips items with zero discrepancy', function () {
    $count = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');

    $this->countEngine->recordCounts($count, $count->items->map(fn ($i) => [
        'id' => $i->id, 'physical_quantity' => $i->expected_quantity,
    ])->toArray());

    $this->countEngine->verifyCount($count, $this->user->id);

    $adjusted = $this->countEngine->applyAdjustments($count);

    expect($adjusted)->toBe(0);
});

test('applyAdjustments throws for non-verified count', function () {
    $count = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');

    $this->countEngine->applyAdjustments($count);
})->throws(InvalidArgumentException::class);

test('completeCount completes an adjusted count', function () {
    $count = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');

    $this->countEngine->recordCounts($count, $count->items->map(fn ($i) => [
        'id' => $i->id, 'physical_quantity' => $i->expected_quantity,
    ])->toArray());

    $this->countEngine->verifyCount($count, $this->user->id);
    $this->countEngine->applyAdjustments($count);
    $this->countEngine->completeCount($count);

    expect($count->fresh()->status)->toBe(CountStatusEnum::Completed);
});

test('completeCount completes a verified count with no discrepancies', function () {
    $count = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');

    $this->countEngine->recordCounts($count, $count->items->map(fn ($i) => [
        'id' => $i->id, 'physical_quantity' => $i->expected_quantity,
    ])->toArray());

    $this->countEngine->verifyCount($count, $this->user->id);

    // Complete without adjustments if no discrepancies
    $this->countEngine->completeCount($count);

    expect($count->fresh()->status)->toBe(CountStatusEnum::Completed);
});

test('completeCount throws for draft count', function () {
    $count = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');

    $this->countEngine->completeCount($count);
})->throws(InvalidArgumentException::class);

test('cancelCount cancels a draft count', function () {
    $count = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');

    $this->countEngine->cancelCount($count);

    expect($count->fresh()->status)->toBe(CountStatusEnum::Cancelled);
});

test('cancelCount throws for completed count', function () {
    $count = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');

    $this->countEngine->recordCounts($count, $count->items->map(fn ($i) => [
        'id' => $i->id, 'physical_quantity' => $i->expected_quantity,
    ])->toArray());

    $this->countEngine->verifyCount($count, $this->user->id);
    $this->countEngine->completeCount($count);

    $this->countEngine->cancelCount($count);
})->throws(InvalidArgumentException::class);

test('generateReference produces unique references', function () {
    $countA = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');
    $countB = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');

    expect($countA->reference)->not->toBe($countB->reference);
});

test('getDiscrepanciesForWarehouse returns counts with discrepancies', function () {
    $count = $this->countEngine->createCount(warehouseId: $this->warehouse->id, type: 'full');

    $item = $count->items->first();
    $this->countEngine->recordCounts($count, [
        ['id' => $item->id, 'physical_quantity' => $item->expected_quantity + 5],
    ]);

    $discrepancies = $this->countEngine->getDiscrepanciesForWarehouse($this->warehouse->id);

    expect($discrepancies)->toHaveCount(1);
});
