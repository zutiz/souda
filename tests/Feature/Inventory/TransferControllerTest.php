<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Models\InventoryBalance;
use App\Modules\Inventory\Models\InventoryTransfer;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->sharedSubscribed()->create();
    tenancy()->initialize($this->user->tenant);
    app(TenantManager::class)->initialize($this->user->tenant);

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('user can list transfers', function () {
    InventoryTransfer::factory()->count(3)->create();

    $response = $this->get(route('inventory.transfers.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Transfers/Index'));
});

test('user can view the transfer create page', function () {
    Warehouse::factory()->count(2)->create();

    $response = $this->get(route('inventory.transfers.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Transfers/Create'));
});

test('user can view a transfer', function () {
    $transfer = InventoryTransfer::factory()->create();

    $response = $this->get(route('inventory.transfers.show', $transfer));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Transfers/Show'));
});

test('user can initiate a transfer', function () {
    $fromWarehouse = Warehouse::factory()->create();
    $toWarehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    InventoryBalance::create([
        'product_id' => $product->id,
        'warehouse_id' => $fromWarehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 0,
        'available_quantity' => 100,
    ]);

    $response = $this->post(route('inventory.transfers.store'), [
        'from_warehouse_id' => $fromWarehouse->id,
        'to_warehouse_id' => $toWarehouse->id,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 10],
        ],
        'description' => 'Test transfer',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseCount('inventory_transfers', 1);
});

test('transfer requires from_warehouse_id and to_warehouse_id', function () {
    $response = $this->post(route('inventory.transfers.store'), [
        'items' => [['product_id' => 'test', 'quantity' => 1]],
    ]);

    $response->assertSessionHasErrors(['from_warehouse_id', 'to_warehouse_id']);
});

test('transfer requires at least one item', function () {
    $response = $this->post(route('inventory.transfers.store'), [
        'from_warehouse_id' => 1,
        'to_warehouse_id' => 2,
        'items' => [],
    ]);

    $response->assertSessionHasErrors('items');
});

test('transfer to and from warehouse must be different', function () {
    $warehouse = Warehouse::factory()->create();

    $response = $this->post(route('inventory.transfers.store'), [
        'from_warehouse_id' => $warehouse->id,
        'to_warehouse_id' => $warehouse->id,
        'items' => [['product_id' => 'test', 'quantity' => 1]],
    ]);

    $response->assertSessionHasErrors('to_warehouse_id');
});

test('user can send a transfer', function () {
    $transfer = InventoryTransfer::factory()->create();

    $response = $this->post(route('inventory.transfers.send', $transfer));

    $response->assertSessionHas('success');
});

test('user can receive a transfer', function () {
    $transfer = InventoryTransfer::factory()->inTransit()->create();

    $response = $this->post(route('inventory.transfers.receive', $transfer));

    $response->assertSessionHas('success');
});

test('user can cancel a transfer', function () {
    $transfer = InventoryTransfer::factory()->create();

    $response = $this->post(route('inventory.transfers.cancel', $transfer));

    $response->assertSessionHas('success');
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.transfers.index'))->assertRedirect(route('login'));
    $this->post(route('inventory.transfers.store'), [])->assertRedirect(route('login'));
});
