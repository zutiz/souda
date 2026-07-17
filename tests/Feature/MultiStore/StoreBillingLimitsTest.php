<?php

declare(strict_types=1);

use App\Models\Permission;
use App\Models\Tenant;
use App\Modules\Billing\Enums\BillingCycle;
use App\Modules\Billing\Enums\SubscriptionStatus;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Services\StoreBillingService;
use App\Modules\Store\Models\Store;
use App\Tenancy\TenantManager;

beforeEach(function () {
    Permission::firstOrCreate(['name' => 'stores.view']);
    Permission::firstOrCreate(['name' => 'stores.create']);

    $this->storeBillingService = app(StoreBillingService::class);
});

test('can create store returns true when under plan limit', function () {
    $plan = Plan::factory()->createQuietly([
        'default_stores' => 3,
        'store_price' => 500,
    ]);

    $tenant = Tenant::factory()->shared()
        ->afterCreating(fn (Tenant $t) => $t->subscriptions()->create([
            'plan_id' => $plan->id,
            'gateway' => 'manual',
            'status' => SubscriptionStatus::Active,
            'billing_cycle' => BillingCycle::Monthly,
            'amount' => $plan->monthly_price,
            'currency' => $plan->currency,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'next_billing_at' => now()->addMonth(),
        ]))
        ->create();

    app(TenantManager::class)->initialize($tenant);

    expect($this->storeBillingService->canCreateStore($tenant))->toBeTrue();
    expect($this->storeBillingService->remainingStores($tenant))->toBe(3);

    app(TenantManager::class)->end();
});

test('can create store returns false when at plan limit', function () {
    $plan = Plan::factory()->createQuietly([
        'default_stores' => 1,
        'store_price' => 500,
    ]);

    $tenant = Tenant::factory()->shared()
        ->afterCreating(fn (Tenant $t) => $t->subscriptions()->create([
            'plan_id' => $plan->id,
            'gateway' => 'manual',
            'status' => SubscriptionStatus::Active,
            'billing_cycle' => BillingCycle::Monthly,
            'amount' => $plan->monthly_price,
            'currency' => $plan->currency,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'next_billing_at' => now()->addMonth(),
        ]))
        ->create();

    app(TenantManager::class)->initialize($tenant);

    Store::factory()->create(['tenant_id' => $tenant->id, 'status' => 'active']);

    expect($this->storeBillingService->canCreateStore($tenant))->toBeFalse();
    expect($this->storeBillingService->remainingStores($tenant))->toBe(0);

    app(TenantManager::class)->end();
});

test('remaining stores returns correct count', function () {
    $plan = Plan::factory()->createQuietly([
        'default_stores' => 5,
        'store_price' => 350,
    ]);

    $tenant = Tenant::factory()->shared()
        ->afterCreating(fn (Tenant $t) => $t->subscriptions()->create([
            'plan_id' => $plan->id,
            'gateway' => 'manual',
            'status' => SubscriptionStatus::Active,
            'billing_cycle' => BillingCycle::Monthly,
            'amount' => $plan->monthly_price,
            'currency' => $plan->currency,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'next_billing_at' => now()->addMonth(),
        ]))
        ->create();

    app(TenantManager::class)->initialize($tenant);

    Store::factory()->count(2)->create(['tenant_id' => $tenant->id, 'status' => 'active']);

    expect($this->storeBillingService->remainingStores($tenant))->toBe(3);

    app(TenantManager::class)->end();
});

test('calculate store amount returns correct values', function () {
    $plan = Plan::factory()->createQuietly([
        'default_stores' => 2,
        'store_price' => 500,
    ]);

    $tenant = Tenant::factory()->shared()
        ->afterCreating(fn (Tenant $t) => $t->subscriptions()->create([
            'plan_id' => $plan->id,
            'gateway' => 'manual',
            'status' => SubscriptionStatus::Active,
            'billing_cycle' => BillingCycle::Monthly,
            'amount' => $plan->monthly_price,
            'currency' => $plan->currency,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'next_billing_at' => now()->addMonth(),
        ]))
        ->create();

    app(TenantManager::class)->initialize($tenant);

    Store::factory()->count(4)->create(['tenant_id' => $tenant->id, 'status' => 'active']);

    $result = $this->storeBillingService->calculateStoreAmount($tenant, $plan);

    expect($result['active_stores'])->toBe(4);
    expect($result['default_stores'])->toBe(2);
    expect($result['extra_stores'])->toBe(2);
    expect($result['store_amount'])->toBe(1000);

    app(TenantManager::class)->end();
});

test('remaining stores returns zero when no active subscription', function () {
    $tenant = Tenant::factory()->shared()->create();

    expect($this->storeBillingService->remainingStores($tenant))->toBe(0);
    expect($this->storeBillingService->canCreateStore($tenant))->toBeFalse();
});

test('allocate store creates allocation when over plan limit', function () {
    $plan = Plan::factory()->createQuietly([
        'default_stores' => 1,
        'store_price' => 500,
    ]);

    $tenant = Tenant::factory()->shared()
        ->afterCreating(fn (Tenant $t) => $t->subscriptions()->create([
            'plan_id' => $plan->id,
            'gateway' => 'manual',
            'status' => SubscriptionStatus::Active,
            'billing_cycle' => BillingCycle::Monthly,
            'amount' => $plan->monthly_price,
            'currency' => $plan->currency,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'next_billing_at' => now()->addMonth(),
        ]))
        ->create();

    app(TenantManager::class)->initialize($tenant);

    $subscription = $tenant->activeSubscription();
    Store::factory()->create(['tenant_id' => $tenant->id, 'status' => 'active']);

    $extraStore = Store::factory()->create(['tenant_id' => $tenant->id, 'status' => 'active']);

    $allocation = $this->storeBillingService->allocateStore($extraStore, (string) $subscription->id, $tenant);

    expect($allocation)->not->toBeNull();
    expect($allocation->store_id)->toBe($extraStore->id);
    expect($allocation->status)->toBe('active');

    $allocation->delete();
    app(TenantManager::class)->end();
});

test('allocate store returns null when within plan limit', function () {
    $plan = Plan::factory()->createQuietly([
        'default_stores' => 3,
        'store_price' => 500,
    ]);

    $tenant = Tenant::factory()->shared()
        ->afterCreating(fn (Tenant $t) => $t->subscriptions()->create([
            'plan_id' => $plan->id,
            'gateway' => 'manual',
            'status' => SubscriptionStatus::Active,
            'billing_cycle' => BillingCycle::Monthly,
            'amount' => $plan->monthly_price,
            'currency' => $plan->currency,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'next_billing_at' => now()->addMonth(),
        ]))
        ->create();

    app(TenantManager::class)->initialize($tenant);

    $subscription = $tenant->activeSubscription();
    $store = Store::factory()->create(['tenant_id' => $tenant->id, 'status' => 'active']);

    $allocation = $this->storeBillingService->allocateStore($store, (string) $subscription->id, $tenant);

    expect($allocation)->toBeNull();

    app(TenantManager::class)->end();
});

test('release store marks allocation as released', function () {
    $plan = Plan::factory()->createQuietly([
        'default_stores' => 1,
        'store_price' => 500,
    ]);

    $tenant = Tenant::factory()->shared()
        ->afterCreating(fn (Tenant $t) => $t->subscriptions()->create([
            'plan_id' => $plan->id,
            'gateway' => 'manual',
            'status' => SubscriptionStatus::Active,
            'billing_cycle' => BillingCycle::Monthly,
            'amount' => $plan->monthly_price,
            'currency' => $plan->currency,
            'starts_at' => now(),
            'expires_at' => now()->addMonth(),
            'next_billing_at' => now()->addMonth(),
        ]))
        ->create();

    app(TenantManager::class)->initialize($tenant);

    $subscription = $tenant->activeSubscription();
    Store::factory()->create(['tenant_id' => $tenant->id, 'status' => 'active']);
    $extraStore = Store::factory()->create(['tenant_id' => $tenant->id, 'status' => 'active']);

    $allocation = $this->storeBillingService->allocateStore($extraStore, (string) $subscription->id, $tenant);
    expect($allocation->status)->toBe('active');

    $this->storeBillingService->releaseStore($extraStore);
    expect($allocation->fresh()->status)->toBe('released');
    expect($allocation->fresh()->released_at)->not->toBeNull();

    $allocation->delete();
    app(TenantManager::class)->end();
});
