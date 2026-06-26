<?php

namespace App\Jobs;

use App\Events\TenantModeChanged;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;

class MigrateTenantToDedicated extends TenantJob
{
    public function __construct(
        public string $tenantId,
    ) {
        parent::__construct();
        $this->tenantId = $tenantId;
    }

    protected function execute(): void
    {
        $tenant = Tenant::find($this->tenantId);

        if (! $tenant) {
            Log::error('Tenant not found for migration to dedicated', [
                'tenant_id' => $this->tenantId,
            ]);

            $this->fail("Tenant not found: {$this->tenantId}");

            return;
        }

        if ($tenant->isDedicated()) {
            return;
        }

        $previousMode = $tenant->tenancy_mode;

        Log::info('Starting migration to dedicated database', [
            'tenant_id' => $tenant->id,
        ]);

        $manager = $tenant->database()->manager();

        if (! $manager->databaseExists($tenant->database()->getName())) {
            $createJob = app(CreateDatabase::class, ['tenant' => $tenant]);
            $createJob->handle(app(DatabaseManager::class));
        }

        $migrateJob = app(MigrateDatabase::class, ['tenant' => $tenant]);
        $migrateJob->handle();

        tenancy()->initialize($tenant);

        $this->copyDataFromShared($tenant);

        tenancy()->end();

        $tenant->update([
            'tenancy_mode' => 'dedicated',
        ]);

        TenantModeChanged::dispatch($tenant, $previousMode, 'dedicated');

        Log::info('Migration to dedicated database completed', [
            'tenant_id' => $tenant->id,
        ]);
    }

    protected function copyDataFromShared(Tenant $tenant): void
    {
        DB::connection('shared')->table('tenant_settings')
            ->where('tenant_id', $tenant->id)
            ->each(function ($row) {
                $data = json_decode(json_encode($row), true);
                $tenantId = $data['tenant_id'];
                unset($data['id'], $data['tenant_id']);

                DB::table('tenant_settings')->insert($data);
            });

        DB::connection('shared')->table('tasks')
            ->where('tenant_id', $tenant->id)
            ->each(function ($row) {
                $data = json_decode(json_encode($row), true);
                unset($data['id'], $data['tenant_id']);

                DB::table('tasks')->insert($data);
            });
    }
}
