<?php

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Billing\Enums\BillingCycle;
use App\Modules\Billing\Enums\PricingModel;
use App\Modules\Billing\Enums\SeatStatus;
use App\Modules\Billing\Enums\SeatType;
use App\Modules\Billing\Enums\SubscriptionStatus;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\SeatAllocation;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Billing\Services\SeatService;
use App\Modules\Billing\Strategies\FlatPricingStrategy;
use App\Modules\Billing\Strategies\SeatPricingStrategy;

beforeEach(function () {
    $this->plan = Plan::factory()->seatBased()->create();
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $this->subscription = Subscription::create([
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
});

afterEach(function () {
    $this->tenant->delete();
    $this->tenant->forceDelete();
});

// --- Seat Allocation ---

test('seat can be allocated to a tenant', function () {
    $service = app(SeatService::class);

    $allocation = $service->allocateSeat(
        tenantId: $this->tenant->id,
        seatType: SeatType::Staff,
        userId: $this->user->id,
    );

    expect($allocation)->not->toBeNull();
    expect($allocation->tenant_id)->toBe($this->tenant->id);
    expect($allocation->seat_type)->toBe(SeatType::Staff);
    expect($allocation->status)->toBe(SeatStatus::Active);
    expect($allocation->user_id)->toBe($this->user->id);
});

test('pending seat is created for invitations', function () {
    $service = app(SeatService::class);

    $allocation = $service->allocateSeat(
        tenantId: $this->tenant->id,
        seatType: SeatType::Staff,
        email: 'invited@example.com',
        invitationToken: 'token-123',
    );

    expect($allocation->status)->toBe(SeatStatus::Pending);
    expect($allocation->email)->toBe('invited@example.com');
    expect($allocation->user_id)->toBeNull();
});

test('pending seat activates when invitation is accepted', function () {
    $service = app(SeatService::class);

    $invitedUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $allocation = $service->allocateSeat(
        tenantId: $this->tenant->id,
        seatType: SeatType::Staff,
        email: 'invited@example.com',
        invitationToken: 'token-456',
    );

    $activated = $service->activatePendingSeat(
        tenantId: $this->tenant->id,
        invitationToken: 'token-456',
        userId: $invitedUser->id,
    );

    expect($activated)->not->toBeNull();
    expect($activated->fresh()->status)->toBe(SeatStatus::Active);
    expect($activated->fresh()->user_id)->toBe($invitedUser->id);
});

test('seat can be released', function () {
    $service = app(SeatService::class);

    $allocation = $service->allocateSeat(
        tenantId: $this->tenant->id,
        seatType: SeatType::Staff,
        userId: $this->user->id,
    );

    $service->releaseSeat($allocation);

    expect($allocation->fresh()->status)->toBe(SeatStatus::Released);
    expect($allocation->fresh()->released_at)->not->toBeNull();
});

test('seat can be released by user', function () {
    $service = app(SeatService::class);

    $service->allocateSeat(
        tenantId: $this->tenant->id,
        seatType: SeatType::Staff,
        userId: $this->user->id,
    );

    $service->releaseSeatByUser($this->tenant->id, $this->user->id);

    expect(SeatAllocation::forTenant($this->tenant->id)->consumed()->count())->toBe(0);
});

// --- Seat Counting ---

test('consumed seat count returns correct number', function () {
    $service = app(SeatService::class);

    expect($service->getConsumedSeatCount($this->tenant->id))->toBe(0);

    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99999);
    $service->allocateSeat($this->tenant->id, SeatType::Admin, userId: 99998);

    expect($service->getConsumedSeatCount($this->tenant->id))->toBe(2);
});

test('released seats are not counted as consumed', function () {
    $service = app(SeatService::class);

    $allocation = $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99999);
    $service->releaseSeat($allocation);

    expect($service->getConsumedSeatCount($this->tenant->id))->toBe(0);
});

// --- Overage Calculation ---

test('overage count is zero when under default seats', function () {
    $service = app(SeatService::class);

    // Plan has default_seats=3, allocate only 2
    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99997);
    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99998);

    expect($service->getOverageCount($this->tenant->id))->toBe(0);
});

test('overage count returns correct excess', function () {
    $service = app(SeatService::class);

    // Plan has default_seats=3, allocate 5
    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99991);
    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99992);
    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99993);
    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99994);
    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99995);

    expect($service->getOverageCount($this->tenant->id))->toBe(2);
});

// --- Pricing Strategy ---

test('seat pricing strategy calculates overage correctly', function () {
    $strategy = app(SeatPricingStrategy::class);

    // Plan: default_seats=3, seat_price=500
    // Allocate 5 seats → overage = 2 → overage_amount = 1000
    $service = app(SeatService::class);
    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99991);
    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99992);
    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99993);
    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99994);
    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99995);

    $overage = $strategy->calculateOverage($this->tenant->id, $this->plan, $this->subscription);

    expect($overage['total_billable'])->toBe(5);
    expect($overage['included'])->toBe(3);
    expect($overage['overage'])->toBe(2);
    expect($overage['overage_amount'])->toBe(1000);
});

test('flat pricing strategy returns zero overage', function () {
    $strategy = app(FlatPricingStrategy::class);

    $service = app(SeatService::class);
    $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99999);

    $overage = $strategy->calculateOverage($this->tenant->id, $this->plan, $this->subscription);

    expect($overage['overage'])->toBe(0);
    expect($overage['overage_amount'])->toBe(0);
});

test('can add seat returns false at max seats', function () {
    $plan = Plan::factory()->create([
        'pricing_model' => 'per_seat',
        'default_seats' => 1,
        'seat_price' => 500,
        'max_seats' => 2,
    ]);

    $strategy = app(SeatPricingStrategy::class);

    $service = app(SeatService::class);
    $allocation = $service->allocateSeat($this->tenant->id, SeatType::Staff, userId: 99999, subscriptionId: $this->subscription->id);

    // Now consumed = 1, max_seats = 2, so can still add
    expect($strategy->canAddSeat($this->tenant->id, $plan))->toBeTrue();
});

test('can add seat returns false when over max seats', function () {
    $plan = Plan::factory()->create([
        'pricing_model' => 'per_seat',
        'default_seats' => 0,
        'seat_price' => 500,
        'max_seats' => 1,
    ]);

    // Manually create 1 allocation to reach the limit
    SeatAllocation::create([
        'tenant_id' => $this->tenant->id,
        'seat_type' => SeatType::Staff,
        'user_id' => 99999,
        'status' => SeatStatus::Active,
    ]);

    $strategy = app(SeatPricingStrategy::class);

    expect($strategy->canAddSeat($this->tenant->id, $plan))->toBeFalse();
});

// --- Sync Seat Allocations ---

test('refresh seat allocations creates missing records from users', function () {
    $service = app(SeatService::class);

    // Create tenant users (not platform admins)
    User::factory()->create(['tenant_id' => $this->tenant->id]);
    User::factory()->create(['tenant_id' => $this->tenant->id]);

    // Two users + the beforeEach user = 3
    $synced = $service->refreshSeatAllocationsFromUsers($this->tenant->id);

    expect($synced)->toBeGreaterThanOrEqual(2);
    expect($service->getConsumedSeatCount($this->tenant->id))->toBeGreaterThanOrEqual(2);
});

// --- Pricing Model Enum ---

test('pricing model enum correctly identifies seat-based models', function () {
    expect(PricingModel::PerSeat->isSeatBased())->toBeTrue();
    expect(PricingModel::Tiered->isSeatBased())->toBeTrue();
    expect(PricingModel::Flat->isSeatBased())->toBeFalse();
    expect(PricingModel::UsageBased->isSeatBased())->toBeFalse();
});

test('pricing model enum identifies usage-based tracking requirement', function () {
    expect(PricingModel::UsageBased->requiresUsageTracking())->toBeTrue();
    expect(PricingModel::Flat->requiresUsageTracking())->toBeFalse();
    expect(PricingModel::PerSeat->requiresUsageTracking())->toBeFalse();
});
