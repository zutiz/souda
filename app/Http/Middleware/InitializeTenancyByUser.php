<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Exceptions\TenantDatabaseDoesNotExistException;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByUser
{
    protected function isAdminRoute(Request $request): bool
    {
        return str_starts_with($request->path(), 'admin');
    }

    protected function ensureTenantDatabaseExists(mixed $tenant): void
    {
        $manager = $tenant->database()->manager();

        if ($manager->databaseExists($tenant->database()->getName())) {
            return;
        }

        $createJob = app(CreateDatabase::class, ['tenant' => $tenant]);
        $createJob->handle(app(DatabaseManager::class));

        $migrateJob = app(MigrateDatabase::class, ['tenant' => $tenant]);
        $migrateJob->handle();
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAdminRoute($request)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user?->tenant_id && ! tenancy()->initialized) {
            try {
                tenancy()->initialize($user->tenant);
            } catch (TenantDatabaseDoesNotExistException $e) {
                $this->ensureTenantDatabaseExists($user->tenant);

                tenancy()->initialize($user->tenant);
            }
        }

        if ($user && ! tenancy()->initialized) {
            abort(403, 'Tenant context could not be established.');
        }

        return $next($request);
    }

    public function terminate(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }
    }
}
