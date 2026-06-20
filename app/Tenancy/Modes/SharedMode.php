<?php

namespace App\Tenancy\Modes;

use App\Models\Tenant;
use App\Tenancy\Contracts\TenantModeStrategy;
use Illuminate\Support\Facades\Cache;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;

class SharedMode implements TenantModeStrategy
{
    protected Tenant $tenant;

    protected string $originalDefaultConnection;

    public function initialize(Tenant $tenant): void
    {
        $this->tenant = $tenant;
        $this->originalDefaultConnection = config('database.default');

        config(['database.default' => 'shared']);

        app()->instance(TenantContract::class, $tenant);
        app()->instance(Tenant::class, $tenant);

        $this->configureCache();
        $this->configureStorage();
    }

    public function end(): void
    {
        config(['database.default' => $this->originalDefaultConnection ?? config('database.default')]);

        app()->forgetInstance(TenantContract::class);
        app()->forgetInstance(Tenant::class);
    }

    public function isShared(): bool
    {
        return true;
    }

    public function isDedicated(): bool
    {
        return false;
    }

    public function databaseConnection(): string
    {
        return 'shared';
    }

    public function cachePrefix(): string
    {
        return 'tenant_shared_'.$this->tenant->id;
    }

    public function storagePrefix(): string
    {
        return 'shared/'.$this->tenant->id;
    }

    public function queuePrefix(): string
    {
        return 'shared-'.$this->tenant->id;
    }

    protected function configureCache(): void
    {
        $prefix = $this->cachePrefix();
        $store = Cache::store()->getStore();

        if (method_exists($store, 'setPrefix')) {
            $store->setPrefix($prefix.'_'.$store->getPrefix());
        }
    }

    protected function configureStorage(): void
    {
        $prefix = $this->storagePrefix();

        foreach (['local', 'public'] as $disk) {
            $originalRoot = config("filesystems.disks.{$disk}.root");

            if ($originalRoot) {
                config(["filesystems.disks.{$disk}.root" => $originalRoot.'/'.$prefix]);
            }
        }
    }
}
