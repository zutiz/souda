<?php

use App\Modules\Billing\Enums\BillingCycle;
use App\Modules\Billing\Enums\SubscriptionStatus;
use App\Modules\Billing\Events\SubscriptionExpired;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->plan = Plan::factory()->create();
    $this->tenantId = (string) Str::uuid();
});

test('active subscription past expires_at moves to grace', function () {
    $subscription = Subscription::create([
        'tenant_id' => $this->tenantId,
        'plan_id' => $this->plan->id,
        'gateway' => 'manual',
        'status' => SubscriptionStatus::Active,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => 999,
        'currency' => 'BDT',
        'starts_at' => now()->subDays(30),
        'expires_at' => now()->subDay(),
    ]);

    $this->artisan('subscription:expire-expired')
        ->expectsOutputToContain('Moved 1 subscriptions to grace period.')
        ->assertSuccessful();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Grace);
});

test('trial subscription past expires_at moves to grace', function () {
    $subscription = Subscription::create([
        'tenant_id' => $this->tenantId,
        'plan_id' => $this->plan->id,
        'gateway' => 'manual',
        'status' => SubscriptionStatus::Trial,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => 999,
        'currency' => 'BDT',
        'starts_at' => now()->subDays(14),
        'expires_at' => now()->subDay(),
        'trial_ends_at' => now()->subDay(),
    ]);

    $this->artisan('subscription:expire-expired')
        ->assertSuccessful();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Grace);
});

test('grace subscription past grace period expires', function () {
    $gracePeriodDays = config('billing.grace_period_days', 3);

    $subscription = Subscription::create([
        'tenant_id' => $this->tenantId,
        'plan_id' => $this->plan->id,
        'gateway' => 'manual',
        'status' => SubscriptionStatus::Grace,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => 999,
        'currency' => 'BDT',
        'starts_at' => now()->subDays($gracePeriodDays + 10),
        'expires_at' => now()->subDays($gracePeriodDays + 1),
    ]);

    $this->artisan('subscription:expire-expired')
        ->expectsOutputToContain('Expired 1 subscriptions past grace period.')
        ->assertSuccessful();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Expired);
});

test('active subscription within valid period is not moved', function () {
    $subscription = Subscription::create([
        'tenant_id' => $this->tenantId,
        'plan_id' => $this->plan->id,
        'gateway' => 'manual',
        'status' => SubscriptionStatus::Active,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => 999,
        'currency' => 'BDT',
        'starts_at' => now(),
        'expires_at' => now()->addMonth(),
    ]);

    $this->artisan('subscription:expire-expired')
        ->assertSuccessful();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Active);
});

test('dry-run mode does not change any subscription statuses', function () {
    $subscription = Subscription::create([
        'tenant_id' => $this->tenantId,
        'plan_id' => $this->plan->id,
        'gateway' => 'manual',
        'status' => SubscriptionStatus::Active,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => 999,
        'currency' => 'BDT',
        'starts_at' => now()->subDays(30),
        'expires_at' => now()->subDay(),
    ]);

    $this->artisan('subscription:expire-expired --dry-run')
        ->expectsOutputToContain('[DRY-RUN] Would move')
        ->assertSuccessful();

    expect($subscription->fresh()->status)->toBe(SubscriptionStatus::Active);
});

test('expired subscription dispatches SubscriptionExpired event', function () {
    Event::fake();

    $gracePeriodDays = config('billing.grace_period_days', 3);

    $subscription = Subscription::create([
        'tenant_id' => $this->tenantId,
        'plan_id' => $this->plan->id,
        'gateway' => 'manual',
        'status' => SubscriptionStatus::Grace,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => 999,
        'currency' => 'BDT',
        'starts_at' => now()->subDays($gracePeriodDays + 10),
        'expires_at' => now()->subDays($gracePeriodDays + 1),
    ]);

    $this->artisan('subscription:expire-expired')
        ->assertSuccessful();

    Event::assertDispatched(SubscriptionExpired::class, function ($event) use ($subscription) {
        return $event->subscription->id === $subscription->id;
    });
});

test('subscriptions that already expired are not affected', function () {
    Subscription::create([
        'tenant_id' => $this->tenantId,
        'plan_id' => $this->plan->id,
        'gateway' => 'manual',
        'status' => SubscriptionStatus::Expired,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => 999,
        'currency' => 'BDT',
        'starts_at' => now()->subDays(60),
        'expires_at' => now()->subDays(30),
    ]);

    $this->artisan('subscription:expire-expired')
        ->assertSuccessful()
        ->expectsOutputToContain('Moved 0 subscriptions to grace period')
        ->expectsOutputToContain('Expired 0 subscriptions past grace period.');
});

test('cancelled subscriptions are not moved to grace or expired', function () {
    Subscription::create([
        'tenant_id' => $this->tenantId,
        'plan_id' => $this->plan->id,
        'gateway' => 'manual',
        'status' => SubscriptionStatus::Cancelled,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => 999,
        'currency' => 'BDT',
        'starts_at' => now()->subDays(30),
        'expires_at' => now()->subDay(),
        'cancelled_at' => now()->subDay(),
    ]);

    $this->artisan('subscription:expire-expired')
        ->assertSuccessful()
        ->expectsOutputToContain('Moved 0 subscriptions to grace period');
});

test('grace period is configurable via billing config', function () {
    config(['billing.grace_period_days' => 7]);

    $activeSubscription = Subscription::create([
        'tenant_id' => $this->tenantId,
        'plan_id' => $this->plan->id,
        'gateway' => 'manual',
        'status' => SubscriptionStatus::Active,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => 999,
        'currency' => 'BDT',
        'starts_at' => now()->subDays(30),
        'expires_at' => now()->subDay(),
    ]);

    $this->artisan('subscription:expire-expired')
        ->assertSuccessful();

    // expires_at was yesterday, grace period is 7 days, so moves to Grace but not Expired
    expect($activeSubscription->fresh()->status)->toBe(SubscriptionStatus::Grace);
});

test('command output table shows correct metrics', function () {
    Subscription::create([
        'tenant_id' => $this->tenantId,
        'plan_id' => $this->plan->id,
        'gateway' => 'manual',
        'status' => SubscriptionStatus::Active,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => 999,
        'currency' => 'BDT',
        'starts_at' => now()->subDays(30),
        'expires_at' => now()->subDay(),
    ]);

    $this->artisan('subscription:expire-expired')
        ->expectsTable(['Metric', 'Count'], [
            ['Moved to grace', '1'],
            ['Expired', '0'],
            ['Mode', 'Live'],
        ])
        ->assertSuccessful();
});
