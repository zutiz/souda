<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;
use App\Modules\Inventory\Models\InventoryAlert;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();
    tenancy()->initialize($this->user->tenant);

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('user can view the alerts index page', function () {
    InventoryAlert::factory()->count(3)->create();

    $response = $this->get(route('inventory.alerts'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Alerts'));
});

test('user can dismiss an alert', function () {
    $alert = InventoryAlert::factory()->create();

    $response = $this->post(route('inventory.alerts.dismiss', $alert));

    $response->assertSessionHas('success');
    expect($alert->fresh()->dismissed_at)->not->toBeNull();
});

test('user can resolve an alert', function () {
    $alert = InventoryAlert::factory()->create();

    $response = $this->post(route('inventory.alerts.resolve', $alert));

    $response->assertSessionHas('success');
    expect($alert->fresh()->resolved_at)->not->toBeNull();
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.alerts'))->assertRedirect(route('login'));
});
