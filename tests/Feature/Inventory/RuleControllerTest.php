<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureTenantHasSubscription;
use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\User;
use App\Modules\Inventory\Models\InventoryRule;

beforeEach(function () {
    $this->user = User::factory()->subscribed()->create();
    tenancy()->initialize($this->user->tenant);

    $this->withoutMiddleware([
        InitializeTenancyByUser::class,
        EnsureTenantHasSubscription::class,
    ]);

    $this->actingAs($this->user);
});

test('user can list rules', function () {
    InventoryRule::factory()->count(3)->create();

    $response = $this->get(route('inventory.rules.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Rules/Index'));
});

test('user can view the rule create page', function () {
    $response = $this->get(route('inventory.rules.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Rules/Create'));
});

test('user can view a rule', function () {
    $rule = InventoryRule::factory()->create();

    $response = $this->get(route('inventory.rules.show', $rule));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->component('Inventory/Rules/Show'));
});

test('user can create a rule', function () {
    $response = $this->post(route('inventory.rules.store'), [
        'name' => 'Low Stock Alert',
        'condition_type' => 'low_stock',
        'condition_config' => ['threshold' => 10],
        'action_type' => 'create_alert',
        'action_config' => ['severity' => 'warning'],
        'schedule' => 'every_fifteen_minutes',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseCount('inventory_rules', 1);
});

test('rule requires name, condition_type, and action_type', function () {
    $response = $this->post(route('inventory.rules.store'), []);

    $response->assertSessionHasErrors(['name', 'condition_type', 'action_type', 'schedule']);
});

test('user can update a rule', function () {
    $rule = InventoryRule::factory()->create();

    $response = $this->put(route('inventory.rules.update', $rule), [
        'name' => 'Updated Rule',
        'condition_config' => ['threshold' => 20],
        'action_config' => ['severity' => 'critical'],
        'schedule' => 'hourly',
    ]);

    $response->assertSessionHas('success');
    expect($rule->fresh()->name)->toBe('Updated Rule');
});

test('user can toggle a rule enable/disable', function () {
    $rule = InventoryRule::factory()->create(['is_active' => true]);

    $response = $this->post(route('inventory.rules.toggle', $rule));

    $response->assertSessionHas('success');
    expect($rule->fresh()->is_active)->toBeFalse();
});

test('user can toggle a disabled rule back to enabled', function () {
    $rule = InventoryRule::factory()->inactive()->create();

    $response = $this->post(route('inventory.rules.toggle', $rule));

    $response->assertSessionHas('success');
    expect($rule->fresh()->is_active)->toBeTrue();
});

test('user can evaluate a rule', function () {
    $rule = InventoryRule::factory()->create();

    $response = $this->post(route('inventory.rules.evaluate', $rule));

    $response->assertSessionHas('success');
});

test('user can delete a rule', function () {
    $rule = InventoryRule::factory()->create();

    $response = $this->delete(route('inventory.rules.destroy', $rule));

    $response->assertSessionHas('success');
    $this->assertDatabaseCount('inventory_rules', 0);
});

test('unauthenticated user is redirected to login', function () {
    auth()->logout();

    $this->get(route('inventory.rules.index'))->assertRedirect(route('login'));
    $this->post(route('inventory.rules.store'), [])->assertRedirect(route('login'));
});
