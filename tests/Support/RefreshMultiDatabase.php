<?php

namespace Tests\Support;

use App\Models\Task;
use App\Tenancy\TenantManager;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

trait RefreshMultiDatabase
{
    protected static bool $migratedCentral = false;

    protected static bool $migratedShared = false;

    protected function setUpTraits(): array
    {
        $this->refreshDatabase();

        return parent::setUpTraits();
    }

    protected function refreshDatabase(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        if (! static::$migratedCentral) {
            $this->artisan('migrate:fresh', [
                '--database' => 'central',
                '--path' => 'database/migrations',
                '--force' => true,
            ]);

            $this->artisan('migrate', [
                '--database' => 'central',
                '--path' => 'database/migrations/central',
                '--force' => true,
            ]);

            $this->app[Kernel::class]->setArtisan(null);

            static::$migratedCentral = true;
        }

        $this->cleanCentralData();
        $this->setupSharedDatabase();
        $this->dropTenantDatabases();
    }

    protected function cleanCentralData(): void
    {
        DB::connection('central')->table('billing_subscriptions')->delete();
        DB::connection('central')->table('billing_plans')->delete();
        DB::connection('central')->table('users')->delete();
        DB::connection('central')->table('tenants')->delete();
        DB::connection('central')->table('model_has_roles')->delete();
        DB::connection('central')->table('roles')->delete();
        DB::connection('central')->table('app_settings')->delete();
    }

    protected function setupSharedDatabase(): void
    {
        try {
            DB::statement('CREATE DATABASE IF NOT EXISTS `souda_shared` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        } catch (\Throwable $e) {
            return;
        }

        if (! static::$migratedShared) {
            Artisan::call('migrate:fresh', [
                '--force' => true,
                '--database' => 'shared',
                '--path' => 'database/migrations/shared',
            ]);

            Artisan::call('migrate', [
                '--force' => true,
                '--database' => 'shared',
                '--path' => 'app/Modules/Store/Database/Migrations/Tenant',
            ]);

            Artisan::call('migrate', [
                '--force' => true,
                '--database' => 'shared',
                '--path' => 'app/Modules/Order/Database/Migrations/Tenant',
            ]);

            Artisan::call('migrate', [
                '--force' => true,
                '--database' => 'shared',
                '--path' => 'app/Modules/Inventory/Database/Migrations/Tenant',
            ]);

            static::$migratedShared = true;
        }

        $tables = DB::connection('shared')->select('SHOW TABLES');
        $names = array_map(
            fn ($t) => reset($t),
            $tables
        );

        $names = array_values(array_filter($names, fn (string $name) => $name !== 'migrations'));

        DB::connection('shared')->statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($names as $table) {
            DB::connection('shared')->table($table)->truncate();
        }
        DB::connection('shared')->statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected function dropTenantDatabases(): void
    {
        $databases = DB::connection('central')->select(
            "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME LIKE 'souda_tenant_%'"
        );

        foreach ($databases as $db) {
            DB::statement("DROP DATABASE IF EXISTS `{$db->SCHEMA_NAME}`");
        }
    }

    protected function withinTenant($tenant, callable $callback): void
    {
        $manager = app(TenantManager::class);
        $manager->initialize($tenant);
        $callback();
        $manager->end();
    }

    protected function assertTenantDatabaseHas($tenant, string $table, array $data): void
    {
        $manager = app(TenantManager::class);
        $manager->initialize($tenant);

        $connection = $manager->isShared() ? 'shared' : null;
        $this->assertDatabaseHas($table, $data, $connection);

        $manager->end();
    }

    protected function assertTenantDatabaseMissing($tenant, string $table, array $data): void
    {
        $manager = app(TenantManager::class);
        $manager->initialize($tenant);

        $connection = $manager->isShared() ? 'shared' : null;
        $this->assertDatabaseMissing($table, $data, $connection);

        $manager->end();
    }

    protected function createTaskForTenant($tenant, array $attributes = [])
    {
        $task = null;

        $this->withinTenant($tenant, function () use ($attributes, &$task) {
            $task = Task::factory()->create($attributes);
        });

        return $task;
    }
}
