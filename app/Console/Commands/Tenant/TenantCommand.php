<?php

namespace App\Console\Commands\Tenant;

use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

abstract class TenantCommand extends Command
{
    /**
     * Execute a callback for each tenant with proper tenancy initialization.
     */
    protected function forEachTenant(callable $callback, bool $failOnError = false): void
    {
        $manager = app(TenantManager::class);
        $tenants = Tenant::query()->cursor();

        foreach ($tenants as $tenant) {
            try {
                $manager->initialize($tenant);
                $callback($tenant);
            } catch (\Throwable $e) {
                Log::error("Tenant command failed for {$tenant->id}", [
                    'error' => $e->getMessage(),
                    'command' => $this->signature,
                ]);

                $this->error("Failed for tenant {$tenant->id}: {$e->getMessage()}");

                if ($failOnError) {
                    throw $e;
                }
            } finally {
                $manager->end();
            }
        }
    }

    /**
     * Execute a callback for a specific tenant.
     */
    protected function forTenant(string $tenantId, callable $callback): void
    {
        $manager = app(TenantManager::class);
        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            $this->error("Tenant not found: {$tenantId}");

            return;
        }

        try {
            $manager->initialize($tenant);
            $callback($tenant);
        } finally {
            $manager->end();
        }
    }
}
