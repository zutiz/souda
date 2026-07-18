<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();
    tenancy()->initialize($this->user->tenant);

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('user can download dashboard CSV export', function () {
    $response = $this->get(route('inventory.dashboard.export.csv'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    $response->assertHeader('Content-Disposition', 'attachment; filename="inventory-dashboard.csv"');
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.dashboard.export.csv'))->assertRedirect(route('login'));
});
