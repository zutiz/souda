<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;
use App\Modules\Inventory\Models\Warehouse;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();
    tenancy()->initialize($this->user->tenant);

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('user can list warehouses', function () {
    Warehouse::factory()->count(3)->create();

    $response = $this->get(route('inventory.warehouses.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Warehouses/Index'));
});

test('user can view a warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    $response = $this->get(route('inventory.warehouses.show', $warehouse));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Warehouses/Show'));
});

test('user can create a warehouse', function () {
    $response = $this->post(route('inventory.warehouses.store'), [
        'name' => 'New Warehouse',
        'city' => 'Dhaka',
        'country' => 'BD',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseCount('inventory_warehouses', 1);
});

test('warehouse requires name', function () {
    $response = $this->post(route('inventory.warehouses.store'), []);

    $response->assertSessionHasErrors('name');
});

test('warehouse slug must be unique', function () {
    Warehouse::factory()->create(['slug' => 'main-wh']);

    $response = $this->post(route('inventory.warehouses.store'), [
        'name' => 'Another Warehouse',
        'slug' => 'main-wh',
    ]);

    $response->assertSessionHasErrors('slug');
});

test('warehouse code must be unique', function () {
    Warehouse::factory()->create(['code' => 'WH001']);

    $response = $this->post(route('inventory.warehouses.store'), [
        'name' => 'Another Warehouse',
        'code' => 'WH001',
    ]);

    $response->assertSessionHasErrors('code');
});

test('user can update a warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    $response = $this->put(route('inventory.warehouses.update', $warehouse), [
        'name' => 'Updated Warehouse',
        'city' => 'Chittagong',
    ]);

    $response->assertSessionHas('success');
    expect($warehouse->fresh()->name)->toBe('Updated Warehouse');
});

test('user can delete a warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    $response = $this->delete(route('inventory.warehouses.destroy', $warehouse));

    $response->assertSessionHas('success');
    expect(Warehouse::find($warehouse->id))->toBeNull();
});

test('warehouse uses soft deletes', function () {
    $warehouse = Warehouse::factory()->create();

    $this->delete(route('inventory.warehouses.destroy', $warehouse));

    expect(Warehouse::withTrashed()->find($warehouse->id))->not->toBeNull();
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.warehouses.index'))->assertRedirect(route('login'));
    $this->post(route('inventory.warehouses.store'), [])->assertRedirect(route('login'));
});
