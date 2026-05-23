<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\TenantSetting;
use App\Modules\Billing\Events\SubscriptionActivated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;

class ProvisionTenantDatabase
{
    /**
     * Create and migrate the tenant database when a subscription is activated.
     *
     * This runs synchronously so the DB is ready before the user is redirected.
     */
    public function handle(SubscriptionActivated $event): void
    {
        $subscription = $event->subscription;
        $tenant = $subscription->tenant;

        if (! $tenant) {
            return;
        }

        $manager = $tenant->database()->manager();

        if (! $manager->databaseExists($tenant->database()->getName())) {
            try {
                $createJob = app(CreateDatabase::class, ['tenant' => $tenant]);
                $createJob->handle(app(DatabaseManager::class));
            } catch (\Throwable $e) {
                Log::error('Failed to create tenant database', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);

                throw $e;
            }
        }

        try {
            $migrateJob = app(MigrateDatabase::class, ['tenant' => $tenant]);
            $migrateJob->handle();
        } catch (\Throwable $e) {
            Log::error('Failed to migrate tenant database', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }

        $this->seedTenantDefaults($tenant);
    }

    protected function seedTenantDefaults(mixed $tenant): void
    {
        try {
            tenancy()->initialize($tenant);

            if (Schema::hasTable('tenant_settings')) {
                TenantSetting::create(TenantSetting::getDefaults());
            }

            Log::info('Tenant defaults seeded', ['tenant_id' => $tenant->id]);
        } catch (\Throwable $e) {
            Log::error('Failed to seed tenant defaults', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        } finally {
            tenancy()->end();
        }
    }
}
