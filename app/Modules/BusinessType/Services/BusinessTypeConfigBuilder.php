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
            return $this->buildDefaultConfig($tenant);
        }

        $pack = $this->packRegistry->get($businessType->slug);

        if ($pack === null) {
            return $this->buildDefaultConfig($tenant);
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
            'branding' => $this->resolveBranding($tenant, $pack->branding()),
        ];

        $config = $this->applyPlanGating($config, $plan);

        $config = $this->applyTenantOverrides($tenant, $config);

        $config['enabled_modules'] = $this->resolveModules($config['enabled_modules'], $pack);

        return TenantConfig::fromArray($config);
    }

    public function buildDefaultConfig(?Tenant $tenant = null): TenantConfig
    {
        return new TenantConfig(
            businessType: 'general',
            enabledModules: ['product', 'inventory', 'order', 'pos', 'crm', 'team', 'billing'],
            menus: [],
            permissions: [],
            fieldDefinitions: [],
            dashboardWidgets: [],
            posConfig: [],
            reportDefinitions: [],
            workflows: [],
            settings: ['features' => []],
            branding: $tenant !== null ? $this->resolveBranding($tenant, $this->defaultBranding()) : [],
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

    protected function defaultBranding(): array
    {
        return [
            'primary' => 'oklch(0.205 0 0)',
            'primary_foreground' => 'oklch(0.985 0 0)',
            'accent' => 'oklch(0.97 0 0)',
            'accent_foreground' => 'oklch(0.205 0 0)',
            'sidebar' => 'oklch(0.985 0 0)',
            'sidebar_foreground' => 'oklch(0.145 0 0)',
            'sidebar_accent' => 'oklch(0.97 0 0)',
            'radius' => '0.625rem',
        ];
    }

    protected function resolveBranding(Tenant $tenant, array $baseBranding): array
    {
        $branding = $baseBranding;

        // Check tenant_settings for custom branding
        $tenantSetting = $tenant->tenantSetting;

        if ($tenantSetting !== null) {
            // Custom logo overrides both pack default and tenant logo
            if (! empty($tenantSetting->brand_logo_url)) {
                $branding['logo_url'] = $tenantSetting->brand_logo_url;
            }

            // Custom primary color overrides pack default
            if (! empty($tenantSetting->brand_primary_color)) {
                $branding['primary'] = $this->hexToOklch($tenantSetting->brand_primary_color);
                $branding['primary_foreground'] = $this->calculateForeground($branding['primary']);
                $branding['sidebar_primary'] = $branding['primary'];
                $branding['sidebar_primary_foreground'] = $branding['primary_foreground'];
            }

            // Custom accent color overrides pack default
            if (! empty($tenantSetting->brand_accent_color)) {
                $branding['accent'] = $this->hexToOklch($tenantSetting->brand_accent_color);
                $branding['accent_foreground'] = $this->calculateForeground($branding['accent']);
                $branding['sidebar_accent'] = $branding['accent'];
                $branding['sidebar_accent_foreground'] = $branding['accent_foreground'];
            }
        }

        // Fallback: use tenant logo if no custom branding logo
        if (empty($branding['logo_url']) && ! empty($tenant->logo)) {
            $branding['logo_url'] = $tenant->logo;
        }

        return $branding;
    }

    protected function hexToOklch(string $hex): string
    {
        // Simple conversion: for custom brand colors, store as-is
        // The frontend will handle the conversion to oklch if needed
        // For now, we'll pass through and the frontend can use it directly
        return $hex;
    }

    protected function calculateForeground(string $background): string
    {
        $hex = ltrim($background, '#');

        if (strlen($hex) === 6 && ctype_xdigit($hex)) {
            [$r, $g, $b] = array_map(fn (string $part): float => hexdec($part) / 255, str_split($hex, 2));

            $toLinear = fn (float $channel): float => $channel <= 0.03928
                ? $channel / 12.92
                : (($channel + 0.055) / 1.055) ** 2.4;

            $luminance = 0.2126 * $toLinear($r) + 0.7152 * $toLinear($g) + 0.0722 * $toLinear($b);

            return $luminance > 0.4 ? 'oklch(0.205 0 0)' : 'oklch(0.985 0 0)';
        }

        return 'oklch(0.205 0 0)';
    }
}
