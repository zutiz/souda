<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\Permission;
use App\Models\User;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'products.view']);
    Permission::firstOrCreate(['name' => 'products.create']);
    Permission::firstOrCreate(['name' => 'products.update']);
    Permission::firstOrCreate(['name' => 'products.delete']);

    $this->user = User::factory()->subscribed()->create();
    $this->user->givePermissionTo('products.view', 'products.create', 'products.update', 'products.delete');

    tenancy()->initialize($this->user->tenant);

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('authenticated user can create a product', function () {
    $productData = [
        'name' => 'Test Product',
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => 1000,
    ];

    $response = $this->post(route('products.store'), $productData);

    $response->assertSessionHas('success');
    $this->assertDatabaseCount('products', 1);
});

test('product requires valid name and price', function () {
    $response = $this->post(route('products.store'), [
        'name' => '',
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => -1,
    ]);

    $response->assertSessionHasErrors(['name', 'base_price']);
});

test('product sku must be unique', function () {
    Product::factory()->create(['sku' => 'TEST-001']);

    $response = $this->post(route('products.store'), [
        'name' => 'Another Product',
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => 2000,
        'sku' => 'TEST-001',
    ]);

    $response->assertSessionHasErrors('sku');
});

test('user can update product details', function () {
    $product = Product::factory()->create();

    $response = $this->put(route('products.update', $product), [
        'name' => 'Updated Name',
        'type' => 'simple',
        'status' => 'active',
        'base_price' => 2000,
    ]);

    $response->assertSessionHas('success');
    expect($product->fresh()->name)->toBe('Updated Name');
});

test('user can delete a product', function () {
    $product = Product::factory()->create();

    $response = $this->delete(route('products.destroy', $product));

    $response->assertSessionHas('success');
    expect(Product::query()->find($product->id))->toBeNull();
});

test('unauthenticated user cannot access products', function () {
    auth()->logout();

    $response = $this->get(route('products.index'));

    $response->assertRedirect();
});
