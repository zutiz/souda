<?php

namespace App\Http\Middleware;

use App\Modules\Billing\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantHasSubscription
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (! $tenant?->id || ! $this->subscriptionService->tenantHasAccessibleSubscription($tenant->id)) {
            return redirect()->route('billing');
        }

        return $next($request);
    }
}
