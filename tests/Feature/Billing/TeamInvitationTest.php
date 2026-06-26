<?php

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Billing\Enums\SeatStatus;
use App\Modules\Billing\Enums\SeatType;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\SeatAllocation;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    // Create a user with a subscribed tenant (seat-based plan with 3 default seats)
    $this->user = User::factory()->subscribed()->create();
    $this->tenant = $this->user->tenant;
});

afterEach(function () {
    $this->tenant->delete();
    $this->tenant->forceDelete();
});

// --- Team Index ---

test('subscribed user can view team page', function () {
    $this->actingAs($this->user)
        ->get(route('team.index'))
        ->assertOk();
});

test('non-subscribed user is redirected from team page', function () {
    $nonSubscribed = User::factory()->create();

    $this->actingAs($nonSubscribed)
        ->get(route('team.index'))
        ->assertRedirect(route('billing'));
});

test('guest users are redirected to login from team page', function () {
    $this->get(route('team.index'))
        ->assertRedirect(route('login'));
});

test('team index shows seat allocations', function () {
    $allocation = SeatAllocation::create([
        'tenant_id' => $this->tenant->id,
        'seat_type' => SeatType::Staff,
        'user_id' => $this->user->id,
        'status' => SeatStatus::Active,
        'allocated_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->get(route('team.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('team/index')
            ->has('members', 1)
        );
});

// --- Team Invite ---

test('subscribed user can invite a team member', function () {
    $this->actingAs($this->user)
        ->post(route('team.invite'), [
            'email' => 'colleague@example.com',
            'seat_type' => 'staff',
        ])
        ->assertOk()
        ->assertJson(['message' => 'Invitation sent successfully.']);

    $allocation = SeatAllocation::forTenant($this->tenant->id)
        ->where('email', 'colleague@example.com')
        ->first();

    expect($allocation)->not->toBeNull();
    expect($allocation->status)->toBe(SeatStatus::Pending);
    expect($allocation->invitation_token)->not->toBeNull();
});

test('invite validates required fields', function () {
    $this->actingAs($this->user)
        ->post(route('team.invite'), [])
        ->assertSessionHasErrors(['email', 'seat_type']);
});

test('invite validates seat_type must be admin or staff', function () {
    $this->actingAs($this->user)
        ->post(route('team.invite'), [
            'email' => 'colleague@example.com',
            'seat_type' => 'owner',
        ])
        ->assertSessionHasErrors(['seat_type']);
});

test('non-subscribed user cannot invite team members', function () {
    $nonSubscribed = User::factory()->create();

    $this->actingAs($nonSubscribed)
        ->post(route('team.invite'), [
            'email' => 'colleague@example.com',
            'seat_type' => 'staff',
        ])
        ->assertRedirect(route('billing'));
});

// --- Team Invite - Seat Gating ---

test('invite is blocked when seat limit is reached', function () {
    // Change the plan to have 0 default seats and 1 max seat
    $plan = Plan::factory()->create([
        'pricing_model' => 'per_seat',
        'default_seats' => 0,
        'max_seats' => 1,
        'seat_price' => 500,
    ]);

    // Give the tenant a subscription to this plan
    $this->tenant->subscriptions()->update(['plan_id' => $plan->id]);

    // Create one active allocation to fill the seat
    SeatAllocation::create([
        'tenant_id' => $this->tenant->id,
        'seat_type' => SeatType::Staff,
        'user_id' => 99999,
        'status' => SeatStatus::Active,
    ]);

    $this->actingAs($this->user)
        ->post(route('team.invite'), [
            'email' => 'overflow@example.com',
            'seat_type' => 'staff',
        ])
        ->assertStatus(422);
});

test('invite succeeds when under seat limit', function () {
    $plan = Plan::factory()->create([
        'pricing_model' => 'per_seat',
        'default_seats' => 5,
        'max_seats' => 10,
        'seat_price' => 500,
    ]);

    $this->tenant->subscriptions()->update(['plan_id' => $plan->id]);

    $this->actingAs($this->user)
        ->post(route('team.invite'), [
            'email' => 'newmember@example.com',
            'seat_type' => 'admin',
        ])
        ->assertOk();
});

// --- Accept Invitation ---

test('pending invitation can be accepted', function () {
    $invitedUser = User::factory()->create(['tenant_id' => $this->tenant->id]);

    $allocation = SeatAllocation::create([
        'tenant_id' => $this->tenant->id,
        'seat_type' => SeatType::Staff,
        'email' => $invitedUser->email,
        'invitation_token' => 'accept-token-123',
        'status' => SeatStatus::Pending,
        'allocated_at' => now(),
    ]);

    $this->actingAs($invitedUser)
        ->post(route('team.accept', ['token' => 'accept-token-123']))
        ->assertOk()
        ->assertJson(['message' => 'Invitation accepted successfully.']);

    expect($allocation->fresh()->status)->toBe(SeatStatus::Active);
    expect($allocation->fresh()->user_id)->toBe($invitedUser->id);
});

test('accept with invalid token returns 404', function () {
    $this->actingAs($this->user)
        ->post(route('team.accept', ['token' => 'invalid-token']))
        ->assertStatus(404);
});

// --- Remove Team Member ---

test('active team member can be removed', function () {
    $allocation = SeatAllocation::create([
        'tenant_id' => $this->tenant->id,
        'seat_type' => SeatType::Staff,
        'user_id' => 99999,
        'status' => SeatStatus::Active,
        'allocated_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->delete(route('team.destroy', ['allocation' => $allocation->id]))
        ->assertOk()
        ->assertJson(['message' => 'Team member removed successfully.']);

    expect($allocation->fresh()->status)->toBe(SeatStatus::Released);
});

test('removing seat from different tenant returns 404', function () {
    $otherTenant = Tenant::factory()->create();
    $allocation = SeatAllocation::create([
        'tenant_id' => $otherTenant->id,
        'seat_type' => SeatType::Staff,
        'user_id' => 99999,
        'status' => SeatStatus::Active,
        'allocated_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->delete(route('team.destroy', ['allocation' => $allocation->id]))
        ->assertStatus(404);

    $otherTenant->delete();
    $otherTenant->forceDelete();
});

// --- Resend Invitation ---

test('pending invitation can be resent', function () {
    $allocation = SeatAllocation::create([
        'tenant_id' => $this->tenant->id,
        'seat_type' => SeatType::Admin,
        'email' => 'pending@example.com',
        'invitation_token' => 'old-token',
        'status' => SeatStatus::Pending,
        'allocated_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->post(route('team.resend', ['allocation' => $allocation->id]))
        ->assertOk()
        ->assertJson(['message' => 'Invitation resent successfully.']);

    expect($allocation->fresh()->invitation_token)->not->toBe('old-token');
});

test('resending non-pending invitation returns 422', function () {
    $allocation = SeatAllocation::create([
        'tenant_id' => $this->tenant->id,
        'seat_type' => SeatType::Staff,
        'user_id' => 99999,
        'status' => SeatStatus::Active,
        'allocated_at' => now(),
    ]);

    $this->actingAs($this->user)
        ->post(route('team.resend', ['allocation' => $allocation->id]))
        ->assertStatus(422);
});

// --- Guest Redirect ---

test('guest users are redirected from team invite route', function () {
    $this->post(route('team.invite'), [
        'email' => 'test@example.com',
        'seat_type' => 'staff',
    ])->assertRedirect(route('login'));
});

test('guest users are redirected from team accept route', function () {
    $this->post(route('team.accept', ['token' => 'some-token']))
        ->assertRedirect(route('login'));
});
