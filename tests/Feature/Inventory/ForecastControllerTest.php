<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;
use App\Modules\Inventory\Models\DemandForecast;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();
    tenancy()->initialize($this->user->tenant);

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('user can list forecasts', function () {
    DemandForecast::factory()->count(3)->create();

    $response = $this->get(route('inventory.forecasts.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Forecasts/Index'));
});

test('user can view a forecast', function () {
    $forecast = DemandForecast::factory()->create();

    $response = $this->get(route('inventory.forecasts.show', $forecast));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Forecasts/Show'));
});

test('user can generate forecasts for all strategies', function () {
    $response = $this->post(route('inventory.forecasts.generate'), [
        'strategy' => 'all',
    ]);

    $response->assertSessionHas('success');
});

test('user can generate forecasts for a specific strategy', function () {
    $response = $this->post(route('inventory.forecasts.generate'), [
        'strategy' => 'moving_average',
    ]);

    $response->assertSessionHas('success');
});

test('user can resolve expired forecasts', function () {
    DemandForecast::factory()->count(3)->create();

    $response = $this->post(route('inventory.forecasts.resolve'), [
        'days_old' => 1,
    ]);

    $response->assertSessionHas('success');
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.forecasts.index'))->assertRedirect(route('login'));
    $this->post(route('inventory.forecasts.generate'), [])->assertRedirect(route('login'));
});
