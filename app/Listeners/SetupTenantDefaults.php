<?php

namespace App\Listeners;

use App\Models\TenantSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Stancl\Tenancy\Events\TenantCreated;

class SetupTenantDefaults
{
    public function handle(TenantCreated $event): void
    {
        $tenant = $event->tenant;

        try {
            tenancy()->initialize($tenant);

            // Seed default settings into the tenant database
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
