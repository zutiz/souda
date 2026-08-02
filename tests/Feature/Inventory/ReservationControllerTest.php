<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Models\StockReservation;

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

test('user can list reservations', function () {
    StockReservation::factory()->count(3)->create();

    $response = $this->get(route('inventory.reservations.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Reservations/Index'));
});

test('user can filter reservations by status', function () {
    StockReservation::factory()->cancelled()->create();
    StockReservation::factory()->count(2)->create();

    $response = $this->get(route('inventory.reservations.index', ['status' => 'cancelled']));

    $response->assertOk();
});

test('user can view a reservation', function () {
    $reservation = StockReservation::factory()->create();

    $response = $this->get(route('inventory.reservations.show', $reservation));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Reservations/Index'));
});

test('user can cancel a reservation', function () {
    $reservation = StockReservation::factory()->create();

    $response = $this->post(route('inventory.reservations.cancel', $reservation));

    $response->assertSessionHas('success');
    expect($reservation->fresh()->status->value)->toBe('cancelled');
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.reservations.index'))->assertRedirect(route('login'));
    $this->post(route('inventory.reservations.cancel', StockReservation::factory()->create()))->assertRedirect(route('login'));
});
