<?php

namespace Tests\Support;

use App\Models\Task;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

trait RefreshMultiDatabase
{
    protected bool $migratedCentral = false;

    protected array $createdTenantDatabases = [];

    /**
     * Hook into setUpTraits() to ensure refreshDatabase() is called.
     *
     * The standard RefreshDatabase trait hooks in via setUpTraits() checking
     * for RefreshDatabase::class in the class uses. Since we use a custom
     * trait name, we need to override setUpTraits() to trigger our own
     * refreshDatabase() and then delegate to the parent.
     */
    protected function setUpTraits(): array
    {
        $this->refreshDatabase();

        return parent::setUpTraits();
    }

    /**
     * Refresh the central database and clean up tenant databases.
     *
     * In multi-DB mode, the standard RefreshDatabase trait doesn't work because:
     * 1. Tenant database creation (DDL) auto-commits MySQL transactions
     * 2. Tenant data lives in separate databases not covered by central transactions
     *
     * This trait handles both central DB migration and tenant DB lifecycle.
     */
    protected function refreshDatabase(): void
    {
        if (! $this->migratedCentral) {
            $this->artisan('migrate:fresh', [
                '--database' => 'central',
                '--path' => 'database/migrations',
                '--force' => true,
            ]);

            $this->app[Kernel::class]->setArtisan(null);

            $this->migratedCentral = true;
        }

        $this->dropTenantDatabases();
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

    /**
     * Initialize tenancy for a tenant within a test.
     *
     * Use this to run assertions or create data in a tenant's database:
     *
     * $this->withinTenant($tenant, function () use ($task) {
     *     $this->assertDatabaseHas('tasks', ['id' => $task->id]);
     * });
     */
    protected function withinTenant($tenant, callable $callback): void
    {
        tenancy()->initialize($tenant);
        $callback();
        tenancy()->end();
    }

    /**
     * Assert a record exists in the tenant's database.
     */
    protected function assertTenantDatabaseHas($tenant, string $table, array $data): void
    {
        $this->withinTenant($tenant, function () use ($table, $data) {
            $this->assertDatabaseHas($table, $data);
        });
    }

    /**
     * Assert a record is missing from the tenant's database.
     */
    protected function assertTenantDatabaseMissing($tenant, string $table, array $data): void
    {
        $this->withinTenant($tenant, function () use ($table, $data) {
            $this->assertDatabaseMissing($table, $data);
        });
    }

    /**
     * Create a task within a tenant's context and return it.
     */
    protected function createTaskForTenant($tenant, array $attributes = [])
    {
        $task = null;

        $this->withinTenant($tenant, function () use ($attributes, &$task) {
            $task = Task::factory()->create($attributes);
        });

        return $task;
    }
}
