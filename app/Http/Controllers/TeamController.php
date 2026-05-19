<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteTeamMemberRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Billing\Enums\SeatStatus;
use App\Modules\Billing\Enums\SeatType;
use App\Modules\Billing\Models\SeatAllocation;
use App\Modules\Billing\Services\SeatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function __construct(
        private readonly SeatService $seatService,
    ) {}

    public function index(): Response
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $members = SeatAllocation::forTenant($tenant->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (SeatAllocation $allocation) => [
                'id' => $allocation->id,
                'user_id' => $allocation->user_id,
                'email' => $allocation->email,
                'name' => $allocation->user?->name,
                'seat_type' => $allocation->seat_type->value,
                'status' => $allocation->status->value,
                'invitation_token' => $allocation->status === SeatStatus::Pending ? $allocation->invitation_token : null,
                'allocated_at' => $allocation->allocated_at?->toISOString(),
                'released_at' => $allocation->released_at?->toISOString(),
            ]);

        return Inertia::render('team/index', [
            'members' => $members,
        ]);
    }

    public function invite(InviteTeamMemberRequest $request): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        $allocation = $this->seatService->allocateSeat(
            tenantId: $tenant->id,
            seatType: $request->enum('seat_type', SeatType::class),
            email: $request->email,
            invitationToken: Str::random(40),
            subscriptionId: $tenant->activeSubscription()?->id,
        );

        return response()->json([
            'message' => 'Invitation sent successfully.',
            'invitation' => [
                'id' => $allocation->id,
                'email' => $allocation->email,
                'token' => $allocation->invitation_token,
                'status' => $allocation->status->value,
            ],
        ]);
    }

    public function accept(Request $request, string $token): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        /** @var User $user */
        $user = $request->user();

        $allocation = $this->seatService->activatePendingSeat(
            tenantId: $tenant->id,
            invitationToken: $token,
            userId: $user->id,
        );

        if (! $allocation) {
            return response()->json(['error' => 'Invalid or expired invitation token.'], 404);
        }

        return response()->json([
            'message' => 'Invitation accepted successfully.',
        ]);
    }

    public function destroy(SeatAllocation $allocation): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        if ($allocation->tenant_id !== $tenant->id) {
            return response()->json(['error' => 'Seat allocation not found.'], 404);
        }

        $this->seatService->releaseSeat($allocation);

        return response()->json(['message' => 'Team member removed successfully.']);
    }

    public function resend(SeatAllocation $allocation): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        if ($allocation->tenant_id !== $tenant->id) {
            return response()->json(['error' => 'Seat allocation not found.'], 404);
        }

        if ($allocation->status !== SeatStatus::Pending) {
            return response()->json(['error' => 'Only pending invitations can be resent.'], 422);
        }

        $allocation->update([
            'invitation_token' => Str::random(40),
        ]);

        return response()->json([
            'message' => 'Invitation resent successfully.',
            'token' => $allocation->fresh()->invitation_token,
        ]);
    }
}
