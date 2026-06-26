<?php

namespace App\Http\Middleware;

use App\Modules\Billing\Exceptions\FeatureNotAccessibleException;
use App\Modules\Billing\Services\PlanFeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantHasFeature
{
    public function __construct(
        private readonly PlanFeatureService $planFeatureService,
    ) {}

    /**
     * Handle an incoming request.
     *
     * Usage in routes:
     *   Route::middleware('feature:inventory_management')->group(...);
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = tenant();

        if (! $tenant?->id) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'No tenant context.'], 403);
            }

            return redirect()->route('billing');
        }

        try {
            $this->planFeatureService->requireFeature($tenant->id, $feature);
        } catch (FeatureNotAccessibleException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'feature' => $feature,
                ], 403);
            }

            return redirect()->route('billing')
                ->with('error', $e->getMessage());
        }

        return $next($request);
    }
}
