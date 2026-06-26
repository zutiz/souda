<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\Permission;
use App\Models\User;
use App\Modules\Product\Models\Brand;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'products.view']);
    Permission::firstOrCreate(['name' => 'products.create']);
    Permission::firstOrCreate(['name' => 'products.update']);
    Permission::firstOrCreate(['name' => 'products.delete']);

    $this->user = User::factory()->subscribed()->create();
    $this->user->givePermissionTo(
        'products.view', 'products.create', 'products.update', 'products.delete',
    );

    tenancy()->initialize($this->user->tenant);

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('create page renders with categories and brands', function () {
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    $response = $this->get(route('products.create'));

    $response->assertOk();

    $props = $response->original->getData()['page']['props'];

    expect($props['categories'])->toHaveCount(1)
        ->and($props['categories'][0]['id'])->toBe($category->id)
        ->and($props['brands'])->toHaveCount(1)
        ->and($props['brands'][0]['id'])->toBe($brand->id);
});

test('can create a product with all fields', function () {
    $category = Category::factory()->create();
    $brand = Brand::factory()->create();

    $response = $this->post(route('products.store'), [
        'name' => 'Premium Widget',
        'type' => 'simple',
        'status' => 'active',
        'base_price' => 2999,
        'compare_at_price' => 3999,
        'cost_price' => 1500,
        'sku' => 'WGT-001',
        'barcode' => '8901234567890',
        'barcode_type' => 'ean13',
        'category_id' => $category->id,
        'brand_id' => $brand->id,
        'description' => 'A premium widget with extra features.',
        'short_description' => 'Premium widget.',
        'tax_inclusive' => true,
        'track_inventory' => true,
        'low_stock_threshold' => 10,
        'weight' => 1.5,
        'dimensions' => ['weight' => 1.5, 'length' => 10, 'width' => 5, 'height' => 3],
        'slug' => 'premium-widget',
        'published_at' => now()->toDateTimeString(),
        'metadata' => ['color' => 'red', 'material' => 'aluminum'],
    ]);

    $response->assertSessionHas('success');

    $product = Product::query()->where('sku', 'WGT-001')->first();

    expect($product)->not->toBeNull()
        ->and($product->name)->toBe('Premium Widget')
        ->and($product->type->value)->toBe('simple')
        ->and($product->status->value)->toBe('active')
        ->and($product->base_price)->toBe(2999)
        ->and($product->compare_at_price)->toBe(3999)
        ->and($product->cost_price)->toBe(1500)
        ->and($product->barcode)->toBe('8901234567890')
        ->and($product->barcode_type)->toBe('ean13')
        ->and((string) $product->category_id)->toBe((string) $category->id)
        ->and((string) $product->brand_id)->toBe((string) $brand->id)
        ->and($product->description)->toBe('A premium widget with extra features.')
        ->and($product->tax_inclusive)->toBeTrue()
        ->and($product->track_inventory)->toBeTrue()
        ->and($product->low_stock_threshold)->toBe(10)
        ->and((float) $product->weight)->toBe(1.5)
        ->and($product->slug)->toBe('premium-widget')
        ->and($product->metadata)->toBe(['color' => 'red', 'material' => 'aluminum'])
        ->and($product->published_at)->not->toBeNull();
});

test('auto-generates slug from name when slug is not provided', function () {
    $this->post(route('products.store'), [
        'name' => 'My Awesome Product',
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => 1999,
    ]);

    $product = Product::query()->first();

    expect($product->slug)->not->toBeEmpty()
        ->and($product->slug)->toMatch('/^my-awesome-product/');
});

test('product requires type and status', function () {
    $response = $this->post(route('products.store'), [
        'name' => 'Test',
        'base_price' => 1000,
    ]);

    $response->assertSessionHasErrors(['type', 'status']);
});

test('type must be a valid product type', function () {
    $response = $this->post(route('products.store'), [
        'name' => 'Test',
        'type' => 'invalid_type',
        'status' => 'draft',
        'base_price' => 1000,
    ]);

    $response->assertSessionHasErrors('type');
});

test('product name cannot exceed 500 characters', function () {
    $response = $this->post(route('products.store'), [
        'name' => str_repeat('A', 501),
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => 1000,
    ]);

    $response->assertSessionHasErrors('name');
});

test('product barcode must be unique', function () {
    Product::factory()->create(['barcode' => 'BAR-CODE-001']);

    $response = $this->post(route('products.store'), [
        'name' => 'Another Product',
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => 2000,
        'barcode' => 'BAR-CODE-001',
    ]);

    $response->assertSessionHasErrors('barcode');
});

test('slug must be unique', function () {
    Product::factory()->create(['slug' => 'existing-product']);

    $response = $this->post(route('products.store'), [
        'name' => 'New Product',
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => 1000,
        'slug' => 'existing-product',
    ]);

    $response->assertSessionHasErrors('slug');
});

test('barcode type must be valid', function () {
    $response = $this->post(route('products.store'), [
        'name' => 'Test',
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => 1000,
        'barcode_type' => 'invalid',
    ]);

    $response->assertSessionHasErrors('barcode_type');
});

test('can create configurable product', function () {
    $response = $this->post(route('products.store'), [
        'name' => 'Configurable Product',
        'type' => 'configurable',
        'status' => 'draft',
        'base_price' => 5000,
    ]);

    $response->assertSessionHas('success');

    $product = Product::query()->where('name', 'Configurable Product')->first();

    expect($product->type->value)->toBe('configurable');
});

test('can create bundle product', function () {
    $response = $this->post(route('products.store'), [
        'name' => 'Bundle Product',
        'type' => 'bundle',
        'status' => 'draft',
        'base_price' => 7500,
    ]);

    $response->assertSessionHas('success');

    $product = Product::query()->where('name', 'Bundle Product')->first();

    expect($product->type->value)->toBe('bundle');
});

test('can create product with archived status', function () {
    $response = $this->post(route('products.store'), [
        'name' => 'Archived Product',
        'type' => 'simple',
        'status' => 'archived',
        'base_price' => 1000,
    ]);

    $response->assertSessionHas('success');

    $product = Product::query()->where('name', 'Archived Product')->first();

    expect($product->status->value)->toBe('archived');
});

test('can create product with zero base price', function () {
    $response = $this->post(route('products.store'), [
        'name' => 'Free Product',
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => 0,
    ]);

    $response->assertSessionHas('success');

    $product = Product::query()->where('name', 'Free Product')->first();

    expect($product->base_price)->toBe(0);
});

test('can create product with inventory tracking disabled', function () {
    $response = $this->post(route('products.store'), [
        'name' => 'Service Product',
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => 10000,
        'track_inventory' => false,
    ]);

    $response->assertSessionHas('success');

    $product = Product::query()->where('name', 'Service Product')->first();

    expect($product->track_inventory)->toBeFalse();
});

test('can create product with category ids', function () {
    $category = Category::factory()->create();

    $response = $this->post(route('products.store'), [
        'name' => 'Multi Category Product',
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => 3000,
        'category_ids' => [$category->id],
    ]);

    $response->assertSessionHas('success');

    $product = Product::query()->where('name', 'Multi Category Product')->first();

    expect($product->categories)->toHaveCount(1)
        ->and((string) $product->categories->first()->id)->toBe((string) $category->id);
});

test('user without permission cannot create a product', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('products.store'), [
        'name' => 'Unauthorized',
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => 1000,
    ]);

    $response->assertForbidden();
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $response = $this->post(route('products.store'), [
        'name' => 'Test',
        'type' => 'simple',
        'status' => 'draft',
        'base_price' => 1000,
    ]);

    $response->assertRedirect(route('login'));
});

test('can fully update product details', function () {
    $product = Product::factory()->create();

    $category = Category::factory()->create();

    $response = $this->put(route('products.update', $product), [
        'name' => 'Completely Updated',
        'type' => 'configurable',
        'status' => 'active',
        'base_price' => 9999,
        'compare_at_price' => 12999,
        'cost_price' => 5000,
        'sku' => 'UPDATED-SKU',
        'category_id' => $category->id,
        'description' => 'Updated description.',
        'tax_inclusive' => false,
        'track_inventory' => true,
        'low_stock_threshold' => 3,
        'slug' => 'completely-updated',
    ]);

    $response->assertSessionHas('success');

    $product->refresh();

    expect($product->name)->toBe('Completely Updated')
        ->and($product->type->value)->toBe('configurable')
        ->and($product->status->value)->toBe('active')
        ->and($product->base_price)->toBe(9999)
        ->and($product->compare_at_price)->toBe(12999)
        ->and($product->cost_price)->toBe(5000)
        ->and($product->sku)->toBe('UPDATED-SKU')
        ->and($product->description)->toBe('Updated description.')
        ->and($product->slug)->toBe('completely-updated');
});

test('user without permission cannot update a product', function () {
    $product = Product::factory()->create();

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->put(route('products.update', $product), [
        'name' => 'Hacked',
        'type' => 'simple',
        'status' => 'active',
        'base_price' => 1000,
    ]);

    $response->assertForbidden();
});

test('user without permission cannot delete a product', function () {
    $product = Product::factory()->create();

    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->delete(route('products.destroy', $product));

    $response->assertForbidden();
});
