<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Services;

use App\Models\Tenant;
use App\Modules\BusinessType\Models\BusinessType;
use App\Modules\BusinessType\Models\TenantConfig as TenantConfigModel;
use App\Modules\BusinessType\ValueObjects\TenantConfig;
use Illuminate\Support\Facades\Cache;

class BusinessTypeEngine
{
    public function __construct(
        protected BusinessTypeConfigBuilder $configBuilder,
        protected IndustryPackRegistry $packRegistry,
    ) {}

    public function getEffectiveConfig(Tenant $tenant): TenantConfig
    {
        $cacheKey = "tenant_config:{$tenant->id}";

        return Cache::remember($cacheKey, 86400, function () use ($tenant) {
            $cached = TenantConfigModel::query()
                ->where('tenant_id', $tenant->id)
                ->first();

            if ($cached !== null) {
                return TenantConfig::fromArray($cached->config);
            }

            $config = $this->configBuilder->build($tenant);

            TenantConfigModel::query()->updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'business_type_slug' => $config->businessType,
                    'config' => $config->toArray(),
                    'config_hash' => md5(serialize($config->toArray())),
                ],
            );

            return $config;
        });
    }

    public function invalidateConfig(Tenant $tenant): void
    {
        Cache::forget("tenant_config:{$tenant->id}");

        TenantConfigModel::query()
            ->where('tenant_id', $tenant->id)
            ->delete();
    }

    public function rebuildConfig(Tenant $tenant): TenantConfig
    {
        $this->invalidateConfig($tenant);

        return $this->getEffectiveConfig($tenant);
    }

    public function assignBusinessType(Tenant $tenant, string $businessTypeSlug): void
    {
        $businessType = BusinessType::query()
            ->where('slug', $businessTypeSlug)
            ->first();

        if ($businessType === null) {
            throw new \RuntimeException("Business type [{$businessTypeSlug}] not found.");
        }

        $tenant->business_type_id = $businessType->id;
        $tenant->save();

        $pack = $this->packRegistry->get($businessTypeSlug);
        if ($pack !== null) {
            $pack->onTenantAssigned($tenant);
        }

        $this->rebuildConfig($tenant);
    }

    public function getPackRegistry(): IndustryPackRegistry
    {
        return $this->packRegistry;
    }
}
