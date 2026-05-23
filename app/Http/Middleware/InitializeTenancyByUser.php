<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Exceptions\TenantDatabaseDoesNotExistException;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByUser
{
    protected function isAdminRoute(Request $request): bool
    {
        return str_starts_with($request->path(), 'admin');
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAdminRoute($request)) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user?->tenant_id) {
            if ($user) {
                abort(403, 'Tenant context could not be established.');
            }

            return $next($request);
        }

        $tenant = $user->tenant;

        if (! $tenant) {
            abort(403, 'Tenant not found. Your account may have been deactivated.');
        }

        try {
            tenancy()->initialize($tenant);
        } catch (TenantDatabaseDoesNotExistException) {
            // Tenant database doesn't exist yet — user hasn't subscribed.
            // Only allow billing routes; redirect everything else.
            if ($request->routeIs('billing') || $request->routeIs('billing.*')) {
                return $next($request);
            }

            return redirect()->route('billing');
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }
    }
}
