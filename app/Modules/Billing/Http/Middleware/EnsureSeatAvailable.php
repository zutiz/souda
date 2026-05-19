<?php

namespace App\Modules\Billing\Http\Middleware;

use App\Modules\Billing\Exceptions\SeatLimitExceededException;
use App\Modules\Billing\Services\SeatService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSeatAvailable
{
    public function __construct(
        private readonly SeatService $seatService,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (! $tenant?->id) {
            return $next($request);
        }

        $subscription = $tenant->activeSubscription();

        if (! $subscription?->plan) {
            return $next($request);
        }

        $plan = $subscription->plan;
        $strategy = $this->seatService->strategy($plan);

        if (! $strategy->canAddSeat($tenant->id, $plan)) {
            throw new SeatLimitExceededException(
                maxSeats: $strategy->getMaxSeats($plan),
                currentSeats: $plan->default_seats,
            );
        }

        return $next($request);
    }
}
