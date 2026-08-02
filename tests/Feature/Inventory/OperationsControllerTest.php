<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Artisan;

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

test('user can view the operations page', function () {
    $response = $this->get(route('inventory.operations'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Operations'));
});

test('user can run a known command', function () {
    Artisan::shouldReceive('call')
        ->once()
        ->with('inventory:expire-reservations')
        ->andReturn(0);

    Artisan::shouldReceive('output')
        ->once()
        ->andReturn('Expired 0 reservations');

    $response = $this->post(route('inventory.operations.run', 'inventory:expire-reservations'));

    $response->assertOk();
    expect($response->json('success'))->toBeTrue();
});

test('user receives 404 for unknown command', function () {
    $response = $this->post(route('inventory.operations.run', 'inventory:unknown-command'));

    $response->assertNotFound();
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.operations'))->assertRedirect(route('login'));
});
