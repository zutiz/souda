<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\Permission;
use App\Models\User;
use App\Modules\Store\Models\Store;
use App\Modules\Store\Services\StoreContextManager;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'stores.view']);
    Permission::firstOrCreate(['name' => 'stores.switch']);

    $this->user = User::factory()->subscribed()->create();
    $this->user->givePermissionTo('stores.view', 'stores.switch');

    tenancy()->initialize($this->user->tenant);

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('store context manager can initialize with a store', function () {
    $store = Store::factory()->create();
    $manager = app(StoreContextManager::class);

    $manager->initialize($store);

    expect($manager->initialized())->toBeTrue();
    expect($manager->id())->toBe($store->id);
    expect($manager->current()->id)->toBe($store->id);
});

test('store context manager returns null when not initialized', function () {
    $manager = app(StoreContextManager::class);

    expect($manager->initialized())->toBeFalse();
    expect($manager->id())->toBeNull();
    expect($manager->current())->toBeNull();
});

test('store context manager can be ended', function () {
    $store = Store::factory()->create();
    $manager = app(StoreContextManager::class);

    $manager->initialize($store);
    expect($manager->initialized())->toBeTrue();

    $manager->end();
    expect($manager->initialized())->toBeFalse();
});

test('store context manager can reinitialize with a different store', function () {
    $storeA = Store::factory()->create();
    $storeB = Store::factory()->create();
    $manager = app(StoreContextManager::class);

    $manager->initialize($storeA);
    expect($manager->id())->toBe($storeA->id);

    $manager->initialize($storeB);
    expect($manager->id())->toBe($storeB->id);
});

test('user can switch store via endpoint', function () {
    $store = Store::factory()->create();

    $response = $this->post(route('stores.switch', $store));

    $response->assertSessionHas('success');
    expect(session('current_store_id'))->toBe($store->id);
});

test('switching store sets session and initializes context', function () {
    $store = Store::factory()->create();

    $this->post(route('stores.switch', $store));

    $manager = app(StoreContextManager::class);

    expect($manager->initialized())->toBeTrue();
    expect($manager->id())->toBe($store->id);
    expect(session('current_store_id'))->toBe($store->id);
});
