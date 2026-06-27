<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\BusinessType\Models\BusinessType;
use App\Tenancy\TenantManager;

beforeEach(function () {
    $user = User::factory()->subscribed()->create();
    $user->tenants()->attach($user->tenant_id, [
        'role' => 'owner',
        'is_default' => true,
    ]);

    tenancy()->initialize($user->tenant);
    app(TenantManager::class)->initialize($user->tenant);

    $this->actingAs($user);
});

test('business settings can update business name', function () {
    $tenant = app(TenantManager::class)->current();

    $this->patch(route('business.update'), [
        'name' => 'Updated Business Name',
    ])->assertSessionHas('success');

    expect($tenant->fresh()->name)->toBe('Updated Business Name');
});

test('business settings validates business type slug via closure', function () {
    $type = BusinessType::create([
        'slug' => 'grocery',
        'name' => 'Grocery',
        'description' => 'Grocery store',
        'icon' => 'ShoppingCart',
        'is_active' => true,
    ]);

    $this->patch(route('business.update'), [
        'name' => 'Test',
        'business_type_slug' => 'grocery',
    ])->assertSessionHas('success');
});

test('business settings rejects invalid business type slug', function () {
    $this->patch(route('business.update'), [
        'name' => 'Test',
        'business_type_slug' => 'non_existent_type',
    ])->assertSessionHasErrors('business_type_slug');
});

test('business settings works without business type slug', function () {
    $this->patch(route('business.update'), [
        'name' => 'Just Name Update',
    ])->assertSessionHas('success');
});

test('business settings rejects empty name', function () {
    $this->patch(route('business.update'), [
        'name' => '',
    ])->assertSessionHasErrors('name');
});
