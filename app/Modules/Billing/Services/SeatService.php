<?php

namespace App\Modules\Billing\Services;

use App\Models\User;
use App\Modules\Billing\Contracts\PricingStrategy;
use App\Modules\Billing\Enums\PricingModel;
use App\Modules\Billing\Enums\SeatStatus;
use App\Modules\Billing\Enums\SeatType;
use App\Modules\Billing\Events\SeatAllocated;
use App\Modules\Billing\Events\SeatReleased;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\SeatAllocation;
use App\Modules\Billing\Strategies\FlatPricingStrategy;
use App\Modules\Billing\Strategies\SeatPricingStrategy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeatService
{
    private array $strategies = [];

    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly PlanService $planService,
    ) {
        $this->strategies = [
            PricingModel::PerSeat->value => app(SeatPricingStrategy::class),
            PricingModel::Flat->value => app(FlatPricingStrategy::class),
        ];
    }

    public function strategy(?Plan $plan = null): PricingStrategy
    {
        $model = $plan?->pricing_model ?? PricingModel::Flat->value;

        return $this->strategies[$model] ?? $this->strategies[PricingModel::Flat->value];
    }

    public function allocateSeat(
        string $tenantId,
        SeatType $seatType,
        ?int $userId = null,
        ?string $email = null,
        ?string $invitationToken = null,
        ?int $subscriptionId = null,
    ): SeatAllocation {
        $plan = $this->resolvePlan($tenantId);
        $strategy = $this->strategy($plan);

        if (! $strategy->canAddSeat($tenantId, $plan)) {
            throw new \RuntimeException('Seat limit reached for this plan.');
        }

        $overage = $this->getOverageCount($tenantId, $plan);

        $allocation = DB::connection('central')->transaction(function () use (
            $tenantId, $seatType, $userId, $email, $invitationToken, $subscriptionId
        ) {
            $allocation = SeatAllocation::create([
                'tenant_id' => $tenantId,
                'seat_type' => $seatType,
                'user_id' => $userId,
                'email' => $email,
                'invitation_token' => $invitationToken,
                'subscription_id' => $subscriptionId,
                'status' => $userId ? SeatStatus::Active : SeatStatus::Pending,
                'allocated_at' => now(),
                'billing_start_at' => now(),
                'metadata' => [],
            ]);

            return $allocation;
        });

        $isOverage = $overage > 0;

        Log::info('Seat allocated', [
            'tenant_id' => $tenantId,
            'seat_id' => $allocation->id,
            'seat_type' => $seatType->value,
            'is_overage' => $isOverage,
        ]);

        SeatAllocated::dispatch($allocation, $isOverage);

        return $allocation;
    }

    public function releaseSeat(SeatAllocation $allocation): void
    {
        DB::connection('central')->transaction(function () use ($allocation) {
            $allocation->release();
        });

        Log::info('Seat released', [
            'tenant_id' => $allocation->tenant_id,
            'seat_id' => $allocation->id,
        ]);

        SeatReleased::dispatch($allocation);
    }

    public function releaseSeatByUser(string $tenantId, int $userId): void
    {
        $allocation = SeatAllocation::forTenant($tenantId)
            ->where('user_id', $userId)
            ->consumed()
            ->first();

        if ($allocation) {
            $this->releaseSeat($allocation);
        }
    }

    public function activatePendingSeat(string $tenantId, string $invitationToken, int $userId): ?SeatAllocation
    {
        $allocation = SeatAllocation::forTenant($tenantId)
            ->where('invitation_token', $invitationToken)
            ->byStatus(SeatStatus::Pending)
            ->first();

        if (! $allocation) {
            return null;
        }

        $allocation->update([
            'user_id' => $userId,
            'status' => SeatStatus::Active,
            'invitation_token' => null,
        ]);

        return $allocation;
    }

    public function getConsumedSeatCount(string $tenantId): int
    {
        return SeatAllocation::forTenant($tenantId)
            ->consumed()
            ->count();
    }

    public function getBillableSeatCount(string $tenantId): int
    {
        return SeatAllocation::forTenant($tenantId)
            ->consumed()
            ->whereIn('seat_type', SeatType::billableTypes())
            ->count();
    }

    public function getOverageCount(string $tenantId, ?Plan $plan = null): int
    {
        $plan = $plan ?? $this->resolvePlan($tenantId);

        if (! $plan || ! $plan->pricing_model || ! PricingModel::from($plan->pricing_model)->isSeatBased()) {
            return 0;
        }

        $billable = $this->getBillableSeatCount($tenantId);
        $included = $plan->default_seats ?? 1;

        return max(0, $billable - $included);
    }

    public function refreshSeatAllocationsFromUsers(string $tenantId): int
    {
        $synced = 0;

        $billableUsers = $this->getBillableUsers($tenantId);

        foreach ($billableUsers as $user) {
            $exists = SeatAllocation::forTenant($tenantId)
                ->where('user_id', $user['id'])
                ->consumed()
                ->exists();

            if (! $exists) {
                SeatAllocation::create([
                    'tenant_id' => $tenantId,
                    'seat_type' => $user['seat_type'],
                    'user_id' => $user['id'],
                    'status' => SeatStatus::Active,
                    'allocated_at' => now(),
                ]);

                $synced++;
            }
        }

        return $synced;
    }

    private function resolvePlan(string $tenantId): ?Plan
    {
        $subscription = $this->subscriptionService->getTenantSubscription($tenantId);

        return $subscription?->plan;
    }

    private function getBillableUsers(string $tenantId): array
    {
        $users = User::where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->get();

        return $users
            ->filter(fn ($user) => $this->userConsumesSeat($user))
            ->map(fn ($user) => [
                'id' => $user->id,
                'seat_type' => $this->resolveSeatType($user),
            ])
            ->values()
            ->all();
    }

    private function userConsumesSeat(User $user): bool
    {
        // Platform admins, system users, and API users do not consume seats
        if ($user->hasRole('admin')) {
            return false;
        }

        // Users without any billable role are assumed staff (seats consumed)
        return true;
    }

    private function resolveSeatType(User $user): SeatType
    {
        $tenant = $user->tenant;

        if ($tenant && $tenant->owner_id === $user->id) {
            return SeatType::Owner;
        }

        return SeatType::Staff;
    }
}
