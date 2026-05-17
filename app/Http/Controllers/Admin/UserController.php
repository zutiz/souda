<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Billing\Enums\SubscriptionStatus;
use App\Modules\Billing\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function index(): Response
    {
        $excludeAdmins = fn ($query) => $query->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'));

        $active = User::with('tenant')
            ->tap($excludeAdmins)
            ->latest()
            ->paginate(15);

        $deactivated = User::onlyTrashed()
            ->with(['tenant' => fn ($q) => $q->withTrashed()])
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))
            ->latest('deleted_at')
            ->get();

        $mapUser = function (User $user) {
            $tenant = $user->tenant;
            $isTrashed = $user->trashed();
            $subscription = $tenant?->activeSubscription();

            $status = 'inactive';
            $planName = null;

            if ($subscription) {
                if ($subscription->status === SubscriptionStatus::Active) {
                    $status = 'active';
                } elseif ($subscription->onTrial()) {
                    $status = 'trialing';
                }
                $planName = $subscription->plan?->name;
            } elseif (! $isTrashed && $tenant?->trial_ends_at?->isFuture()) {
                $status = 'trialing';
            }

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tenant_id' => $tenant?->id,
                'subscription_status' => $status,
                'plan_name' => $planName,
                'created_at' => $user->created_at->toISOString(),
                'deactivated_at' => $user->deleted_at?->toISOString(),
            ];
        };

        return Inertia::render('admin/users/index', [
            'users' => $active->through($mapUser),
            'deactivated' => $deactivated->map($mapUser)->values(),
        ]);
    }

    public function show(string $id): Response
    {
        $user = User::withTrashed()
            ->with(['tenant' => fn ($q) => $q->withTrashed()])
            ->findOrFail($id);

        abort_if($user->hasRole('admin'), 403, 'Admin users cannot be managed.');

        $tenant = $user->tenant;
        $subscription = $tenant?->activeSubscription();
        $isDeactivated = $user->trashed();

        $subscriptionData = null;

        if ($subscription) {
            $plan = $subscription->plan;

            $subscriptionData = [
                'status' => $subscription->status->value,
                'gateway' => $subscription->gateway,
                'plan_name' => $plan?->name,
                'billing_cycle' => $subscription->billing_cycle->value,
                'amount' => $subscription->amount,
                'on_trial' => $subscription->onTrial(),
                'trial_ends_at' => $subscription->trial_ends_at?->toISOString(),
                'expires_at' => $subscription->expires_at?->toISOString(),
                'cancelled_at' => $subscription->cancelled_at?->toISOString(),
                'is_accessible' => $subscription->isAccessible(),
                'is_cancelled' => $subscription->status === SubscriptionStatus::Cancelled,
                'starts_at' => $subscription->starts_at?->toISOString(),
                'created_at' => $subscription->created_at->toISOString(),
            ];
        }

        return Inertia::render('admin/users/show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'is_deactivated' => $isDeactivated,
                'deactivated_at' => $user->deleted_at?->toISOString(),
                'created_at' => $user->created_at->toISOString(),
                'updated_at' => $user->updated_at->toISOString(),
            ],
            'tenant' => $tenant ? [
                'id' => $tenant->id,
                'on_generic_trial' => ! $isDeactivated && $tenant->trial_ends_at?->isFuture(),
                'generic_trial_ends_at' => $tenant->trial_ends_at?->toISOString(),
                'created_at' => $tenant->created_at->toISOString(),
                'updated_at' => $tenant->updated_at->toISOString(),
            ] : null,
            'subscription' => $subscriptionData,
        ]);
    }

    public function destroy(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        abort_if($user->hasRole('admin'), 403, 'Admin users cannot be deactivated.');

        $tenant = $user->tenant;
        $subscription = $tenant?->activeSubscription();
        if ($subscription) {
            $this->subscriptionService->cancelSubscription($subscription);
        }

        $user->delete();
        $tenant?->delete();

        return redirect()->route('users.index')->with('success', 'User deactivated successfully.');
    }

    public function restore(string $id): RedirectResponse
    {
        $user = User::onlyTrashed()
            ->with(['tenant' => fn ($q) => $q->withTrashed()])
            ->findOrFail($id);

        abort_if($user->hasRole('admin'), 403, 'Admin users cannot be restored.');

        $user->restore();

        $tenant = $user->tenant;
        if ($tenant?->trashed()) {
            $tenant->restore();
        }

        return redirect()->route('users.show', $user->id)->with('success', 'User restored successfully.');
    }

    public function forceDestroy(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Hash::check($request->password, $request->user()->password)) {
            return back()->withErrors(['password' => 'The password you entered is incorrect.']);
        }

        $user = User::withTrashed()
            ->with(['tenant' => fn ($q) => $q->withTrashed()])
            ->findOrFail($id);

        abort_if($user->hasRole('admin'), 403, 'Admin users cannot be deleted.');

        $tenant = $user->tenant;
        $subscription = $tenant?->activeSubscription();
        if ($subscription) {
            $this->subscriptionService->cancelSubscription($subscription);
        }

        $user->forceDelete();
        $tenant?->forceDelete();

        return redirect()->route('users.index')->with('success', 'User permanently deleted.');
    }
}
