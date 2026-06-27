<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
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

        if (! $user) {
            return $next($request);
        }

        // Resolve tenant: session first (tenant switcher), then user.tenant_id (legacy)
        $tenantId = $request->session()->get('active_tenant_id', $user->tenant_id);

        if (! $tenantId) {
            abort(403, 'Tenant context could not be established.');
        }

        $tenant = Tenant::query()->find($tenantId);

        if (! $tenant) {
            abort(403, 'Tenant not found. Your account may have been deactivated.');
        }

        // Verify user belongs to this tenant
        // Checks both the legacy direct tenant_id and the new pivot table
        $hasAccess = $user->tenant_id === $tenantId
            || $user->tenants()->where('tenant_id', $tenantId)->exists();

        if (! $hasAccess) {
            abort(403, 'You do not have access to this tenant.');
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
