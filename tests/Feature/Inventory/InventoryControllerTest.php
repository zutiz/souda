<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;
use App\Tenancy\TenantManager;

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

test('user can view the inventory dashboard', function () {
    $response = $this->get(route('inventory.dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Dashboard'));
});

test('user can view inventory balances', function () {
    $response = $this->get('/inventory/balances');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Index'));
});

test('user can view inventory movements', function () {
    $response = $this->get(route('inventory.movements'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Movements'));
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.dashboard'))->assertRedirect(route('login'));
});
