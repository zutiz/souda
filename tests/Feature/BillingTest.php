<?php

use App\Models\User;
use App\Modules\Billing\Enums\SubscriptionStatus;

test('billing page loads for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('billing'))
        ->assertOk();
});

test('billing page loads for user with cancelled subscription', function () {
    $user = User::factory()->cancelledSubscription()->create();

    $response = $this->actingAs($user)
        ->get(route('billing'));

    $response->assertOk();

    $props = $response->original->getData()['page']['props'];

    expect($props['subscription']['stripe_status'])->toBe(SubscriptionStatus::Cancelled->value)
        ->and($props['plans'])->toBeArray();
});

test('billing page with session_id loads safely', function () {
    $user = User::factory()->cancelledSubscription()->create();

    $response = $this->actingAs($user)
        ->get(route('billing', ['checkout' => 'success']));

    $response->assertOk();

    $props = $response->original->getData()['page']['props'];

    expect($props['subscription']['stripe_status'])->toBe(SubscriptionStatus::Cancelled->value)
        ->and($props['plans'])->toBeArray();
});
