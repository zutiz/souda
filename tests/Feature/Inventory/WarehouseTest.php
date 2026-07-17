<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Inventory\Models\InventoryBin;
use App\Modules\Inventory\Models\Warehouse;
use Illuminate\Database\QueryException;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();

    tenancy()->initialize($this->user->tenant);
});

test('can create a warehouse', function () {
    $warehouse = Warehouse::factory()->create([
        'name' => 'Main Warehouse',
    ]);

    expect($warehouse)->toBeInstanceOf(Warehouse::class)
        ->and($warehouse->name)->toBe('Main Warehouse')
        ->and($warehouse->slug)->not->toBeNull()
        ->and($warehouse->is_active)->toBeTrue()
        ->and($warehouse->is_default)->toBeFalse();
});

test('warehouse slug is auto-generated from name', function () {
    $warehouse = Warehouse::factory()->create([
        'name' => 'My Cool Warehouse',
        'slug' => '',
    ]);

    expect($warehouse->slug)->toBe('my-cool-warehouse');
});

test('warehouse enforces unique slug per tenant', function () {
    Warehouse::factory()->create(['slug' => 'main-warehouse']);

    expect(fn () => Warehouse::factory()->create(['slug' => 'main-warehouse']))
        ->toThrow(QueryException::class);
});

test('warehouse has bins relationship', function () {
    $warehouse = Warehouse::factory()->create();

    $bin = InventoryBin::factory()->create([
        'warehouse_id' => $warehouse->id,
    ]);

    expect($warehouse->bins)->toHaveCount(1)
        ->and($warehouse->bins->first()->id)->toBe($bin->id);
});

test('warehouse active scope works', function () {
    Warehouse::factory()->create(['is_active' => true, 'slug' => 'active-wh']);
    Warehouse::factory()->inactive()->create(['slug' => 'inactive-wh']);

    expect(Warehouse::active()->get())->toHaveCount(1);
});

test('warehouse default scope works', function () {
    Warehouse::factory()->create(['is_default' => false, 'slug' => 'non-default']);
    Warehouse::factory()->default()->create(['slug' => 'default-wh']);

    expect(Warehouse::default()->get())->toHaveCount(1);
});

test('warehouse supports soft deletes', function () {
    $warehouse = Warehouse::factory()->create();
    $warehouseId = $warehouse->id;

    $warehouse->delete();
    expect(Warehouse::find($warehouseId))->toBeNull()
        ->and(Warehouse::withTrashed()->find($warehouseId))->not->toBeNull();
});

test('can create inventory bins for a warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    $binA = InventoryBin::factory()->create([
        'warehouse_id' => $warehouse->id,
        'code' => 'A-001',
        'zone' => 'A',
        'is_pickable' => true,
    ]);

    $binB = InventoryBin::factory()->create([
        'warehouse_id' => $warehouse->id,
        'code' => 'B-001',
        'zone' => 'B',
        'is_pickable' => false,
    ]);

    expect($warehouse->bins)->toHaveCount(2);

    $pickableBins = InventoryBin::pickable()->get();
    expect($pickableBins)->toHaveCount(1)
        ->and($pickableBins->first()->code)->toBe('A-001');
});

test('bins can be filtered by zone', function () {
    $warehouse = Warehouse::factory()->create();

    InventoryBin::factory()->create([
        'warehouse_id' => $warehouse->id,
        'code' => 'A-001',
        'zone' => 'A',
    ]);
    InventoryBin::factory()->create([
        'warehouse_id' => $warehouse->id,
        'code' => 'A-002',
        'zone' => 'A',
    ]);
    InventoryBin::factory()->create([
        'warehouse_id' => $warehouse->id,
        'code' => 'B-001',
        'zone' => 'B',
    ]);

    expect(InventoryBin::byZone('A')->get())->toHaveCount(2)
        ->and(InventoryBin::byZone('B')->get())->toHaveCount(1);
});
