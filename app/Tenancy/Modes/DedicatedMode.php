<?php

namespace App\Tenancy\Modes;

use App\Models\Tenant;
use App\Tenancy\Contracts\TenantModeStrategy;

class DedicatedMode implements TenantModeStrategy
{
    public function initialize(Tenant $tenant): void
    {
        tenancy()->initialize($tenant);
    }

    public function end(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }
    }

    public function isShared(): bool
    {
        return false;
    }

    public function isDedicated(): bool
    {
        return true;
    }

    public function databaseConnection(): string
    {
        return config('tenancy.database.template_tenant_connection', 'mysql');
    }

    public function cachePrefix(): string
    {
        return 'tenant';
    }

    public function storagePrefix(): string
    {
        return 'tenant';
    }

    public function queuePrefix(): string
    {
        return 'tenant';
    }
}
