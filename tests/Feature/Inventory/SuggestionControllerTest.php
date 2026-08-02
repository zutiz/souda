<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;
use App\Tenancy\TenantManager;
use App\Modules\Inventory\Models\PurchaseSuggestion;

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

test('user can list suggestions', function () {
    PurchaseSuggestion::factory()->count(3)->create();

    $response = $this->get(route('inventory.suggestions.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Suggestions/Index'));
});

test('user can mark a suggestion as ordered', function () {
    $suggestion = PurchaseSuggestion::factory()->create();

    $response = $this->put(route('inventory.suggestions.update', $suggestion), [
        'status' => 'ordered',
        'order_reference' => 'PO-001',
    ]);

    $response->assertSessionHas('success');
    expect($suggestion->fresh()->status)->toBe('ordered');
});

test('user can dismiss a suggestion', function () {
    $suggestion = PurchaseSuggestion::factory()->create();

    $response = $this->put(route('inventory.suggestions.update', $suggestion), [
        'status' => 'dismissed',
        'notes' => 'Not needed right now',
    ]);

    $response->assertSessionHas('success');
    expect($suggestion->fresh()->status)->toBe('dismissed');
});

test('suggestion requires a valid status', function () {
    $suggestion = PurchaseSuggestion::factory()->create();

    $response = $this->put(route('inventory.suggestions.update', $suggestion), [
        'status' => 'invalid',
    ]);

    $response->assertSessionHasErrors('status');
});

test('user can generate suggestions', function () {
    $response = $this->post(route('inventory.suggestions.generate'));

    $response->assertSessionHas('success');
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.suggestions.index'))->assertRedirect(route('login'));
    $this->post(route('inventory.suggestions.generate'), [])->assertRedirect(route('login'));
});
