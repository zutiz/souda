<?php

namespace App\Jobs;

use App\Events\TenantModeChanged;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateTenantToShared extends TenantJob
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
            Log::error('Tenant not found for migration to shared', [
                'tenant_id' => $this->tenantId,
            ]);

            $this->fail("Tenant not found: {$this->tenantId}");

            return;
        }

        if ($tenant->isShared()) {
            return;
        }

        $previousMode = $tenant->tenancy_mode;

        Log::info('Starting migration to shared database', [
            'tenant_id' => $tenant->id,
        ]);

        tenancy()->initialize($tenant);

        $this->copyDataToShared($tenant);
        $this->dropDedicatedDatabase($tenant);

        tenancy()->end();

        $tenant->update([
            'tenancy_mode' => 'shared',
            'database_name' => null,
        ]);

        TenantModeChanged::dispatch($tenant, $previousMode, 'shared');

        Log::info('Migration to shared database completed', [
            'tenant_id' => $tenant->id,
        ]);
    }

    protected function copyDataToShared(Tenant $tenant): void
    {
        DB::table('tenant_settings')
            ->each(function ($row) use ($tenant) {
                $data = json_decode(json_encode($row), true);
                unset($data['id']);
                $data['tenant_id'] = $tenant->id;

                DB::connection('shared')->table('tenant_settings')->insert($data);
            });

        DB::table('tasks')
            ->each(function ($row) use ($tenant) {
                $data = json_decode(json_encode($row), true);
                unset($data['id']);
                $data['tenant_id'] = $tenant->id;

                DB::connection('shared')->table('tasks')->insert($data);
            });
    }

    protected function dropDedicatedDatabase(Tenant $tenant): void
    {
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
    }
}
