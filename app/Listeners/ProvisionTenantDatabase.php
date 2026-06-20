<?php

namespace App\Listeners;

use App\Events\TenantModeChanged;
use App\Models\TenantSetting;
use App\Modules\Billing\Events\SubscriptionActivated;
use App\Modules\BusinessType\Models\BusinessType;
use App\Modules\BusinessType\Services\BusinessTypeEngine;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;

class ProvisionTenantDatabase
{
    public function handle(SubscriptionActivated $event): void
    {
        $subscription = $event->subscription;
        $tenant = $subscription->tenant;

        if (! $tenant) {
            return;
        }

        $this->ensureSharedDatabaseReady();

        $planSlug = $subscription->plan?->slug ?? 'free';

        /** @var TenantManager $manager */
        $manager = app(TenantManager::class);
        $targetMode = $manager->guessModeFromPlan($planSlug);

        if ($targetMode === 'dedicated' && $tenant->isShared()) {
            $this->upgradeToDedicated($subscription, $tenant);

            return;
        }

        if ($targetMode === 'shared' && $tenant->isDedicated()) {
            $this->downgradeToShared($subscription, $tenant);

            return;
        }

        if ($targetMode === 'shared') {
            $this->provisionSharedTenant($tenant);
        } else {
            $this->provisionDedicatedTenant($subscription, $tenant);
        }

        $this->assignBusinessType($tenant, $subscription);
    }

    protected function ensureSharedDatabaseReady(): void
    {
        try {
            DB::connection('mysql')->statement(
                'CREATE DATABASE IF NOT EXISTS `souda_shared` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );
        } catch (\Throwable $e) {
            Log::warning('Could not create shared database', ['error' => $e->getMessage()]);
        }

        try {
            Artisan::call('migrate', [
                '--force' => true,
                '--path' => 'database/migrations/shared',
                '--database' => 'shared',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Could not run shared migrations', ['error' => $e->getMessage()]);
        }
    }

    protected function assignBusinessType(mixed $tenant, mixed $subscription): void
    {
        if ($tenant->business_type_id) {
            return;
        }

        $businessType = BusinessType::query()->where('is_active', true)->orderBy('id')->first();

        if ($businessType === null) {
            return;
        }

        try {
            app(BusinessTypeEngine::class)->assignBusinessType($tenant, $businessType->slug);
        } catch (\Throwable $e) {
            Log::warning('Failed to assign default business type', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function provisionSharedTenant(mixed $tenant): void
    {
        $defaults = TenantSetting::getDefaults();
        $defaults['tenant_id'] = $tenant->id;

        foreach (['notification_preferences', 'feature_toggles', 'extra'] as $jsonField) {
            if (is_array($defaults[$jsonField] ?? null)) {
                $defaults[$jsonField] = json_encode($defaults[$jsonField]);
            }
        }

        DB::connection('shared')->table('tenant_settings')
            ->updateOrInsert(
                ['tenant_id' => $tenant->id],
                $defaults,
            );

        Log::info('Shared tenant provisioned', ['tenant_id' => $tenant->id]);
    }

    protected function provisionDedicatedTenant(mixed $subscription, mixed $tenant): void
    {
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

    protected function upgradeToDedicated(mixed $subscription, mixed $tenant): void
    {
        $previousMode = $tenant->tenancy_mode;

        $manager = $tenant->database()->manager();

        if (! $manager->databaseExists($tenant->database()->getName())) {
            $createJob = app(CreateDatabase::class, ['tenant' => $tenant]);
            $createJob->handle(app(DatabaseManager::class));
        }

        $migrateJob = app(MigrateDatabase::class, ['tenant' => $tenant]);
        $migrateJob->handle();

        if ($previousMode === 'shared') {
            $this->migrateSharedDataToDedicated($tenant);
        } else {
            $this->seedTenantDefaults($tenant);
        }

        $tenant->update([
            'tenancy_mode' => 'dedicated',
        ]);

        TenantModeChanged::dispatch($tenant, $previousMode, 'dedicated');

        Log::info('Tenant upgraded to dedicated mode', ['tenant_id' => $tenant->id]);
    }

    protected function downgradeToShared(mixed $subscription, mixed $tenant): void
    {
        $previousMode = $tenant->tenancy_mode;

        $this->migrateDedicatedDataToShared($tenant);

        $tenant->update([
            'tenancy_mode' => 'shared',
            'database_name' => null,
        ]);

        try {
            $manager = $tenant->database()->manager();
            $dbName = $tenant->database()->getName();

            if ($manager->databaseExists($dbName)) {
                $manager->deleteDatabase($tenant);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to drop dedicated database during downgrade', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
        }

        TenantModeChanged::dispatch($tenant, $previousMode, 'shared');

        Log::info('Tenant downgraded to shared mode', ['tenant_id' => $tenant->id]);
    }

    protected function migrateSharedDataToDedicated(mixed $tenant): void
    {
        tenancy()->initialize($tenant);

        DB::connection('shared')->table('tenant_settings')
            ->where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->each(function ($row) {
                $data = json_decode(json_encode($row), true);
                unset($data['id'], $data['tenant_id']);

                DB::table('tenant_settings')->insert($data);
            });

        DB::connection('shared')->table('tasks')
            ->where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->each(function ($row) {
                $data = json_decode(json_encode($row), true);
                unset($data['id'], $data['tenant_id']);

                DB::table('tasks')->insert($data);
            });

        tenancy()->end();
    }

    protected function migrateDedicatedDataToShared(mixed $tenant): void
    {
        tenancy()->initialize($tenant);

        DB::table('tenant_settings')
            ->orderBy('id')
            ->each(function ($row) use ($tenant) {
                $data = json_decode(json_encode($row), true);
                unset($data['id']);
                $data['tenant_id'] = $tenant->id;

                DB::connection('shared')->table('tenant_settings')->insert($data);
            });

        DB::table('tasks')
            ->orderBy('id')
            ->each(function ($row) use ($tenant) {
                $data = json_decode(json_encode($row), true);
                unset($data['id']);
                $data['tenant_id'] = $tenant->id;

                DB::connection('shared')->table('tasks')->insert($data);
            });

        tenancy()->end();
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
