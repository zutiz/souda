<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Models\InventoryCount;
use App\Modules\Inventory\Models\InventoryCountItem;
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

test('user can list counts', function () {
    InventoryCount::factory()->count(3)->create();

    $response = $this->get(route('inventory.counts.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Counts/Index'));
});

test('user can view the count create page', function () {
    Warehouse::factory()->create();

    $response = $this->get(route('inventory.counts.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Counts/Create'));
});

test('user can view a count', function () {
    $count = InventoryCount::factory()->create();

    $response = $this->get(route('inventory.counts.show', $count));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Counts/Show'));
});

test('user can create a count', function () {
    $warehouse = Warehouse::factory()->create();

    $response = $this->post(route('inventory.counts.store'), [
        'warehouse_id' => $warehouse->id,
        'type' => 'full',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseCount('inventory_counts', 1);
});

test('count requires warehouse_id and type', function () {
    $response = $this->post(route('inventory.counts.store'), []);

    $response->assertSessionHasErrors(['warehouse_id', 'type']);
});

test('user can record counts for a count', function () {
    $product = Product::factory()->create();
    $count = InventoryCount::factory()->inProgress()->create();
    $item = InventoryCountItem::factory()->create([
        'count_id' => $count->id,
        'product_id' => $product->id,
        'physical_quantity' => 20,
    ]);

    $response = $this->post(route('inventory.counts.record', $count), [
        'items' => [
            ['id' => $item->id, 'physical_quantity' => 25],
        ],
    ]);

    $response->assertSessionHas('success');
});

test('user can verify a count', function () {
    $count = InventoryCount::factory()->inProgress()->create();

    $response = $this->post(route('inventory.counts.verify', $count));

    $response->assertSessionHas('success');
});

test('user can apply adjustments for a count', function () {
    $count = InventoryCount::factory()->verified()->create();

    $response = $this->post(route('inventory.counts.apply', $count));

    $response->assertSessionHas('success');
});

test('user can complete a count', function () {
    $count = InventoryCount::factory()->verified()->create();

    $response = $this->post(route('inventory.counts.complete', $count));

    $response->assertSessionHas('success');
});

test('user can cancel a count', function () {
    $count = InventoryCount::factory()->create();

    $response = $this->post(route('inventory.counts.cancel', $count));

    $response->assertSessionHas('success');
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.counts.index'))->assertRedirect(route('login'));
    $this->post(route('inventory.counts.store'), [])->assertRedirect(route('login'));
});
