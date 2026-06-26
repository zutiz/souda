<?php

use App\Models\User;

test('user with tenant can access billing page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('billing'))
        ->assertOk();
});

test('non-subscribed user is redirected from dashboard to billing', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('billing'));
});

test('subscribed user can access the dashboard', function () {
    $user = User::factory()->subscribed()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('guest users are redirected to login from tenant routes', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));

    $this->get(route('billing'))
        ->assertRedirect(route('login'));
});

test('guest users are redirected to login from task routes', function () {
    $this->get(route('tasks.index'))
        ->assertRedirect(route('login'));
});

test('non-subscribed user is redirected from tasks to billing', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tasks.index'))
        ->assertRedirect(route('billing'));
});

test('subscribed user can access tasks', function () {
    $user = User::factory()->subscribed()->create();

    $this->actingAs($user)
        ->get(route('tasks.index'))
        ->assertOk();
});

test('user without tenant_id gets 403 on tenant routes', function () {
    $user = User::factory()->create(['tenant_id' => null]);

    $this->actingAs($user)
        ->get(route('tasks.index'))
        ->assertForbidden();

    $this->actingAs($user)
        ->get(route('billing'))
        ->assertForbidden();
});

test('user without tenant_id gets 403 on dashboard', function () {
    $user = User::factory()->create(['tenant_id' => null]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertForbidden();
});

test('admin can access admin routes without tenant context', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

test('non-admin user cannot access admin routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('billing page loads for user with cancelled subscription', function () {
    $user = User::factory()->cancelledSubscription()->create();

    $this->actingAs($user)
        ->get(route('billing'))
        ->assertOk();
});

test('user with cancelled subscription is redirected from dashboard to billing', function () {
    $user = User::factory()->cancelledSubscription()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('billing'));
});

test('billing subscribe route is accessible without subscription', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('billing.subscribe'), [
            'plan_id' => 1,
            'gateway' => 'manual',
            'billing_cycle' => 'monthly',
        ]);
})->skip('Requires valid plan ID and payment gateway mock');
