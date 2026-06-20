<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Services;

use App\Models\Tenant;
use App\Modules\BusinessType\Models\TenantModuleOverride;
use App\Modules\BusinessType\ValueObjects\TenantConfig;

class BusinessTypeConfigBuilder
{
    public function __construct(
        protected IndustryPackRegistry $packRegistry,
    ) {}

    public function build(Tenant $tenant): TenantConfig
    {
        $businessType = $tenant->businessType;

        if ($businessType === null) {
            return $this->buildDefaultConfig();
        }

        $pack = $this->packRegistry->get($businessType->slug);

        if ($pack === null) {
            return $this->buildDefaultConfig();
        }

        $plan = $tenant->activeSubscription()?->plan;

        $config = [
            'business_type' => $pack->slug(),
            'enabled_modules' => $pack->modules(),
            'menus' => $pack->menus(),
            'permissions' => $pack->permissions(),
            'field_definitions' => [],
            'dashboard_widgets' => $pack->dashboardWidgets(),
            'pos_config' => $pack->posConfig(),
            'report_definitions' => $pack->reportDefinitions(),
            'workflows' => [],
            'settings' => array_merge(
                $pack->defaultSettings(),
                ['features' => $pack->featureFlags()],
            ),
        ];

        $config = $this->applyPlanGating($config, $plan);

        $config = $this->applyTenantOverrides($tenant, $config);

        $config['enabled_modules'] = $this->resolveModules($config['enabled_modules'], $pack);

        return TenantConfig::fromArray($config);
    }

    public function buildDefaultConfig(): TenantConfig
    {
        return new TenantConfig(
            businessType: 'general',
            enabledModules: ['product', 'inventory', 'crm', 'team', 'billing'],
            menus: [],
            permissions: [],
            fieldDefinitions: [],
            dashboardWidgets: [],
            posConfig: [],
            reportDefinitions: [],
            workflows: [],
            settings: ['features' => []],
        );
    }

    protected function resolveModules(array $modules, $pack): array
    {
        $resolved = [];

        foreach ($modules as $key => $value) {
            if (is_string($key)) {
                $slug = $key;
                $required = $value['required'] ?? false;
            } else {
                $slug = $value;
                $required = false;
            }

            $resolved[] = $slug;
        }

        return array_values(array_unique($resolved));
    }

    protected function applyPlanGating(array $config, $plan): array
    {
        if ($plan === null) {
            return $config;
        }

        return $config;
    }

    protected function applyTenantOverrides(Tenant $tenant, array $config): array
    {
        $overrides = TenantModuleOverride::query()
            ->where('tenant_id', $tenant->id)
            ->get()
            ->keyBy('module_slug');

        if ($overrides->isNotEmpty()) {
            $config['enabled_modules'] = array_values(array_filter(
                $config['enabled_modules'],
                fn (string $module) => $overrides->has($module)
                    ? $overrides->get($module)->is_enabled
                    : true,
            ));
        }

        return $config;
    }
}
