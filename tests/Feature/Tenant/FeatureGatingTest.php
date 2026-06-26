<?php

use App\Http\Middleware\InitializeTenancyByUser;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Billing\Enums\BillingCycle;
use App\Modules\Billing\Enums\SubscriptionStatus;
use App\Modules\Billing\Exceptions\FeatureNotAccessibleException;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Billing\Services\PlanFeatureService;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->plan = Plan::factory()->create([
        'features' => ['inventory', 'reports', 'api_access'],
        'limits' => ['inventory' => 1000, 'users' => 5],
    ]);

    $this->tenant = Tenant::factory()->create();

    Subscription::create([
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->plan->id,
        'gateway' => 'manual',
        'status' => SubscriptionStatus::Active,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => $this->plan->monthly_price,
        'currency' => $this->plan->currency,
        'starts_at' => now(),
        'expires_at' => now()->addMonth(),
    ]);

    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
});

afterEach(function () {
    $this->tenant->delete();
    $this->tenant->forceDelete();
});

test('tenant has feature included in plan', function () {
    expect(PlanFeatureService::tenantHasFeature($this->tenant->id, 'inventory'))->toBeTrue()
        ->and(PlanFeatureService::tenantHasFeature($this->tenant->id, 'reports'))->toBeTrue()
        ->and(PlanFeatureService::tenantHasFeature($this->tenant->id, 'api_access'))->toBeTrue();
});

test('tenant does not have feature not in plan', function () {
    expect(PlanFeatureService::tenantHasFeature($this->tenant->id, 'premium_support'))->toBeFalse()
        ->and(PlanFeatureService::tenantHasFeature($this->tenant->id, 'nonexistent'))->toBeFalse();
});

test('tenant without subscription has no features', function () {
    $tenantWithoutSub = Tenant::factory()->create();

    expect(PlanFeatureService::tenantHasFeature($tenantWithoutSub->id, 'inventory'))->toBeFalse();
    expect(PlanFeatureService::getTenantFeatures($tenantWithoutSub->id))->toBe([]);

    $tenantWithoutSub->delete();
    $tenantWithoutSub->forceDelete();
});

test('requireFeature passes for existing feature', function () {
    expect(fn () => PlanFeatureService::requireFeature($this->tenant->id, 'inventory'))
        ->not->toThrow(FeatureNotAccessibleException::class);
});

test('requireFeature throws for missing feature', function () {
    expect(fn () => PlanFeatureService::requireFeature($this->tenant->id, 'premium'))
        ->toThrow(FeatureNotAccessibleException::class);
});

test('getTenantFeatures returns plan features', function () {
    $features = PlanFeatureService::getTenantFeatures($this->tenant->id);

    expect($features)->toBe(['inventory', 'reports', 'api_access']);
});

test('getFeatureLimit returns correct value', function () {
    expect(PlanFeatureService::getFeatureLimit($this->tenant->id, 'inventory'))->toBe(1000)
        ->and(PlanFeatureService::getFeatureLimit($this->tenant->id, 'users'))->toBe(5);
});

test('getFeatureLimit returns null for unlimited feature', function () {
    expect(PlanFeatureService::getFeatureLimit($this->tenant->id, 'reports'))->toBeNull();
});

test('hasReachedLimit returns true when at limit', function () {
    expect(PlanFeatureService::hasReachedLimit($this->tenant->id, 'inventory', 1000))->toBeTrue()
        ->and(PlanFeatureService::hasReachedLimit($this->tenant->id, 'inventory', 1001))->toBeTrue();
});

test('hasReachedLimit returns false when under limit', function () {
    expect(PlanFeatureService::hasReachedLimit($this->tenant->id, 'inventory', 999))->toBeFalse();
});

test('hasReachedLimit returns false for unlimited features', function () {
    expect(PlanFeatureService::hasReachedLimit($this->tenant->id, 'reports', 999999))->toBeFalse();
});

test('accepted tenant types (model or string) both work', function () {
    expect(PlanFeatureService::tenantHasFeature($this->tenant, 'inventory'))->toBeTrue()
        ->and(PlanFeatureService::tenantHasFeature($this->tenant->id, 'inventory'))->toBeTrue();
});

test('subscription with feature scope returns accessible subscriptions', function () {
    expect($this->tenant->subscriptions()->accessible()->exists())->toBeTrue();
});

test('ensure tenant has feature middleware allows route for allowed feature', function () {
    Route::middleware(['web', 'auth', InitializeTenancyByUser::class, 'feature:inventory'])
        ->get('/_test/feature-allowed', fn () => response('ok'));

    $this->actingAs($this->user)
        ->get('/_test/feature-allowed')
        ->assertOk();
});

test('ensure tenant has feature middleware redirects for missing feature', function () {
    Route::middleware(['web', 'auth', InitializeTenancyByUser::class, 'feature:premium_support'])
        ->get('/_test/feature-denied', fn () => response('ok'));

    $this->actingAs($this->user)
        ->get('/_test/feature-denied')
        ->assertRedirect(route('billing'));
});

test('ensure tenant has feature middleware returns 403 for json requests', function () {
    Route::middleware(['web', 'auth', InitializeTenancyByUser::class, 'feature:premium_support'])
        ->get('/_test/feature-json', fn () => response('ok'));

    $this->actingAs($this->user)
        ->getJson('/_test/feature-json')
        ->assertForbidden()
        ->assertJson(['feature' => 'premium_support']);
});

test('different plans give different feature access', function () {
    $basicPlan = Plan::factory()->create([
        'features' => ['basic_feature'],
    ]);

    $tenantBasic = Tenant::factory()->create();

    Subscription::create([
        'tenant_id' => $tenantBasic->id,
        'plan_id' => $basicPlan->id,
        'gateway' => 'manual',
        'status' => SubscriptionStatus::Active,
        'billing_cycle' => BillingCycle::Monthly,
        'amount' => $basicPlan->monthly_price,
        'currency' => $basicPlan->currency,
        'starts_at' => now(),
        'expires_at' => now()->addMonth(),
    ]);

    expect(PlanFeatureService::tenantHasFeature($tenantBasic->id, 'basic_feature'))->toBeTrue()
        ->and(PlanFeatureService::tenantHasFeature($tenantBasic->id, 'inventory'))->toBeFalse();

    $tenantBasic->delete();
    $tenantBasic->forceDelete();
});
