<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->givePermissionTo('categories.view', 'categories.create', 'categories.update', 'categories.delete');

    actingAs($this->user);
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
