<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        if (! $manager->databaseExists($tenant->database()->getName())) {
            $createJob = app(CreateDatabase::class, ['tenant' => $tenant]);
            $createJob->handle(app(DatabaseManager::class));
        }

        $migrateJob = app(MigrateDatabase::class, ['tenant' => $tenant]);
        $migrateJob->handle();
    }

    protected function isTenantDatabaseMigrated(mixed $tenant): bool
    {
        try {
            $count = DB::connection('tenant')
                ->table('migrations')
                ->count();

            return $count > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isAdminRoute($request)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user?->tenant_id && ! tenancy()->initialized) {
            $tenant = $user->tenant;

            if (! $tenant) {
                abort(403, 'Tenant not found. Your account may have been deactivated.');
            }

            try {
                tenancy()->initialize($tenant);

                if (! $this->isTenantDatabaseMigrated($tenant)) {
                    $this->ensureTenantDatabaseExists($tenant);
                }
            } catch (TenantDatabaseDoesNotExistException $e) {
                $this->ensureTenantDatabaseExists($tenant);

                tenancy()->initialize($tenant);
            }
        }

        if ($user && ! tenancy()->initialized) {
            abort(403, 'Tenant context could not be established.');
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
