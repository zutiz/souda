<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;
use App\Modules\Inventory\Models\SerialNumber;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();
    tenancy()->initialize($this->user->tenant);

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('user can list serials', function () {
    SerialNumber::factory()->count(3)->create();

    $response = $this->get(route('inventory.serials.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Serials/Index'));
});

test('user can filter serials by status', function () {
    SerialNumber::factory()->sold()->create();
    SerialNumber::factory()->count(2)->create();

    $response = $this->get(route('inventory.serials.index', ['status' => 'sold']));

    $response->assertOk();
});

test('user can search serials', function () {
    SerialNumber::factory()->create(['serial_number' => 'SN-FINDME']);
    SerialNumber::factory()->count(2)->create();

    $response = $this->get(route('inventory.serials.index', ['search' => 'SN-FINDME']));

    $response->assertOk();
});

test('user can view a serial', function () {
    $serial = SerialNumber::factory()->create();

    $response = $this->get(route('inventory.serials.show', $serial));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Serials/Show'));
});

test('user can register a serial', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $response = $this->post(route('inventory.serials.store'), [
        'product_id' => $product->id,
        'serial_number' => 'SN-001',
        'warehouse_id' => $warehouse->id,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseCount('serial_numbers', 1);
});

test('serial requires product_id and serial_number', function () {
    $response = $this->post(route('inventory.serials.store'), []);

    $response->assertSessionHasErrors(['product_id', 'serial_number']);
});

test('user can register multiple serials at once', function () {
    $product = Product::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $response = $this->post(route('inventory.serials.store-batch'), [
        'product_id' => $product->id,
        'serial_numbers' => ['SN-BATCH-01', 'SN-BATCH-02', 'SN-BATCH-03'],
        'warehouse_id' => $warehouse->id,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseCount('serial_numbers', 3);
});

test('batch registration requires at least one serial', function () {
    $response = $this->post(route('inventory.serials.store-batch'), [
        'product_id' => 'test',
        'serial_numbers' => [],
    ]);

    $response->assertSessionHasErrors('serial_numbers');
});

test('user can mark a serial as sold', function () {
    $serial = SerialNumber::factory()->create();

    $response = $this->post(route('inventory.serials.mark-sold', $serial), [
        'order_reference' => 'ORD-12345',
    ]);

    $response->assertOk();
    expect($response->json('success'))->toBeTrue();
});

test('mark sold requires order_reference', function () {
    $serial = SerialNumber::factory()->create();

    $response = $this->post(route('inventory.serials.mark-sold', $serial), []);

    $response->assertStatus(422);
});

test('user can mark a serial as returned', function () {
    $serial = SerialNumber::factory()->sold()->create();

    $response = $this->post(route('inventory.serials.mark-returned', $serial));

    $response->assertOk();
    expect($response->json('success'))->toBeTrue();
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.serials.index'))->assertRedirect(route('login'));
    $this->post(route('inventory.serials.store'), [])->assertRedirect(route('login'));
});
