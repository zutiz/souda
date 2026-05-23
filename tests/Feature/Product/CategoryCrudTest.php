<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\Permission;
use App\Models\User;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'categories.view']);
    Permission::firstOrCreate(['name' => 'categories.create']);
    Permission::firstOrCreate(['name' => 'categories.update']);
    Permission::firstOrCreate(['name' => 'categories.delete']);

    $this->user = User::factory()->subscribed()->create();
    $this->user->givePermissionTo('categories.view', 'categories.create', 'categories.update', 'categories.delete');

    tenancy()->initialize($this->user->tenant);

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('user can create nested categories', function () {
    $parent = Category::factory()->create();

    $response = $this->post(route('categories.store'), [
        'name' => 'Child Category',
        'parent_id' => $parent->id,
    ]);

    $response->assertSessionHas('success');
    expect($parent->fresh()->children)->toHaveCount(1);
});

test('category cannot be its own parent', function () {
    $category = Category::factory()->create();

    $response = $this->put(route('categories.update', $category), [
        'name' => 'Updated',
        'parent_id' => $category->id,
    ]);

    $response->assertSessionHasErrors('parent_id');
});

test('deleting a category does not delete its products', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
    ]);

    $response = $this->delete(route('categories.destroy', $category));

    $response->assertSessionHas('success');
    expect($product->fresh())->not->toBeNull();
});
