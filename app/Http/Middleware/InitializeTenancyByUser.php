<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Tenancy\TenantManager;
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

        $manager = app(TenantManager::class);

        if ($tenant->isDedicated()) {
            try {
                $manager->initialize($tenant);
            } catch (TenantDatabaseDoesNotExistException) {
                if ($request->routeIs('billing') || $request->routeIs('billing.*')) {
                    return $next($request);
                }

                return redirect()->route('billing');
            }
        } else {
            $manager->initialize($tenant);
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $manager = app(TenantManager::class);

        if ($manager->initialized()) {
            $manager->end();
        }
    }
}
