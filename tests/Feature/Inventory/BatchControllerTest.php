<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;
use App\Modules\Inventory\Models\InventoryBatch;
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

test('user can list batches', function () {
    InventoryBatch::factory()->count(3)->create();

    $response = $this->get(route('inventory.batches.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Batches/Index'));
});

test('user can filter batches by status', function () {
    InventoryBatch::factory()->quarantined()->create(['batch_number' => 'BAT-Q']);
    InventoryBatch::factory()->count(2)->create();

    $response = $this->get(route('inventory.batches.index', ['status' => 'quarantined']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Batches/Index'));
});

test('user can search batches', function () {
    InventoryBatch::factory()->create(['batch_number' => 'FIND-ME']);
    InventoryBatch::factory()->count(2)->create();

    $response = $this->get(route('inventory.batches.index', ['search' => 'FIND-ME']));

    $response->assertOk();
});

test('user can view a batch', function () {
    $batch = InventoryBatch::factory()->create();

    $response = $this->get(route('inventory.batches.show', $batch));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Batches/Show'));
});

test('user can receive a batch', function () {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    $response = $this->post(route('inventory.batches.store'), [
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'batch_number' => 'BAT-NEW-001',
        'quantity' => 50,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseCount('inventory_batches', 1);
});

test('batch requires product_id and warehouse_id', function () {
    $response = $this->post(route('inventory.batches.store'), [
        'batch_number' => 'BAT-ERR',
        'quantity' => 10,
    ]);

    $response->assertSessionHasErrors(['product_id', 'warehouse_id']);
});

test('user can deduct from a batch', function () {
    $batch = InventoryBatch::factory()->create(['remaining_quantity' => 100]);

    $response = $this->post(route('inventory.batches.deduct', $batch), [
        'quantity' => 10,
    ]);

    $response->assertOk();
    expect($response->json('success'))->toBeTrue();
});

test('user can quarantine a batch', function () {
    $batch = InventoryBatch::factory()->create();

    $response = $this->post(route('inventory.batches.quarantine', $batch));

    $response->assertSessionHas('success');
    expect($batch->fresh()->status->value)->toBe('quarantined');
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.batches.index'))->assertRedirect(route('login'));
    $this->post(route('inventory.batches.store'), [])->assertRedirect(route('login'));
});
