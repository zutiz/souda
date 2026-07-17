<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'stores.view']);
});

test('legacy user with tenant_id can access tenant routes without pivot record', function () {
    $user = User::factory()->subscribed()->create();
    $user->givePermissionTo('stores.view');

    $this->actingAs($user)
        ->get(route('stores.index'))
        ->assertOk();
});

test('user can switch to a tenant they only have pivot access to', function () {
    $user = User::factory()->subscribed()->create();
    $otherTenant = Tenant::factory()->shared()->create();

    $user->tenants()->attach($otherTenant->id, [
        'role' => 'owner',
        'is_default' => false,
    ]);

    $response = $this->actingAs($user)
        ->post(route('tenant.switch'), [
            'tenant_id' => $otherTenant->id,
        ]);

    $response->assertSessionHas('active_tenant_id', $otherTenant->id);
});

test('user with both legacy and pivot can access tenant routes', function () {
    $user = User::factory()->subscribed()->create();
    $user->tenants()->attach($user->tenant_id, [
        'role' => 'owner',
        'is_default' => true,
    ]);
    $user->givePermissionTo('stores.view');

    $this->actingAs($user)
        ->get(route('stores.index'))
        ->assertOk();
});

test('user cannot access another tenant via session manipulation', function () {
    $user = User::factory()->subscribed()->create();
    $otherTenant = Tenant::factory()->shared()->create();

    $this->actingAs($user)
        ->withSession(['active_tenant_id' => $otherTenant->id])
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('admin routes bypass tenant check', function () {
    $admin = User::factory()->admin()->create(['tenant_id' => null]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

test('user without any tenant association gets 403 on tenant routes', function () {
    $user = User::factory()->create(['tenant_id' => null]);

    $this->actingAs($user)
        ->get(route('stores.index'))
        ->assertForbidden();
});
