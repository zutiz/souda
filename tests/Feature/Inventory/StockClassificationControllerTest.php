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

test('user can view the classification page', function () {
    $response = $this->get(route('inventory.classification.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Rules/Classification'));
});

test('user can filter classifications by abc class', function () {
    $response = $this->get(route('inventory.classification.index', [
        'abc_class' => 'A',
    ]));

    $response->assertOk();
});

test('user can refresh stock classification', function () {
    $response = $this->post(route('inventory.classification.refresh'));

    $response->assertSessionHas('success');
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.classification.index'))->assertRedirect(route('login'));
});
