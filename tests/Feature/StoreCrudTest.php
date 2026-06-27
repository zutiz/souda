<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\Permission;
use App\Models\User;
use App\Modules\Store\Models\Store;
use App\Tenancy\TenantManager;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'stores.view']);
    Permission::firstOrCreate(['name' => 'stores.create']);
    Permission::firstOrCreate(['name' => 'stores.update']);
    Permission::firstOrCreate(['name' => 'stores.delete']);
    Permission::firstOrCreate(['name' => 'stores.switch']);

    $this->user = User::factory()->subscribed()->create();
    $this->user->givePermissionTo('stores.view', 'stores.create', 'stores.update', 'stores.delete', 'stores.switch');

    tenancy()->initialize($this->user->tenant);
    app(TenantManager::class)->initialize($this->user->tenant);

    $this->user->tenants()->attach($this->user->tenant_id, [
        'role' => 'owner',
        'is_default' => true,
    ]);

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('user can list stores', function () {
    Store::factory()->count(3)->create();

    $response = $this->get(route('stores.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Store/Index'));
});

test('user can create a store', function () {
    $storeData = [
        'name' => 'Downtown Store',
        'slug' => 'downtown',
        'code' => 'DT-001',
        'email' => 'downtown@example.com',
        'phone' => '+123456789',
        'timezone' => 'America/New_York',
        'currency' => 'USD',
        'status' => 'active',
        'is_default' => false,
    ];

    $response = $this->post(route('stores.store'), $storeData);

    $response->assertSessionHas('success');
    $this->assertDatabaseCount('stores', 1);
});

test('store requires name', function () {
    $response = $this->post(route('stores.store'), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors('name');
});

test('store code must be unique', function () {
    Store::factory()->create(['code' => 'UNIQUE-01']);

    $response = $this->post(route('stores.store'), [
        'name' => 'Another Store',
        'slug' => 'another',
        'code' => 'UNIQUE-01',
    ]);

    $response->assertSessionHasErrors('code');
});

test('user can view a store', function () {
    $store = Store::factory()->create();

    $response = $this->get(route('stores.show', $store));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Store/Show'));
});

test('user can update a store', function () {
    $store = Store::factory()->create();

    $response = $this->put(route('stores.update', $store), [
        'name' => 'Updated Store Name',
        'slug' => 'updated',
        'code' => $store->code,
        'status' => 'active',
    ]);

    $response->assertSessionHas('success');
    expect($store->fresh()->name)->toBe('Updated Store Name');
});

test('user can delete a store', function () {
    $store = Store::factory()->create();

    $response = $this->delete(route('stores.destroy', $store));

    $response->assertSessionHas('success');
    expect(Store::query()->find($store->id))->toBeNull();
});

test('setting a store as default unsets previous default', function () {
    $storeA = Store::factory()->default()->create();
    $storeB = Store::factory()->create();

    $response = $this->post(route('stores.set-default', $storeB));

    $response->assertSessionHas('success');
    expect($storeA->fresh()->is_default)->toBeFalse();
    expect($storeB->fresh()->is_default)->toBeTrue();
});

test('unauthenticated user cannot access stores', function () {
    auth()->logout();

    $response = $this->get(route('stores.index'));

    $response->assertRedirect();
});
