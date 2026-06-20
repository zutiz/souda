<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\ValueObjects;

readonly class TenantConfig
{
    public function __construct(
        public string $businessType,
        public array $enabledModules,
        public array $menus,
        public array $permissions,
        public array $fieldDefinitions,
        public array $dashboardWidgets,
        public array $posConfig,
        public array $reportDefinitions,
        public array $workflows,
        public array $settings,
    ) {}

    public function hasModule(string $module): bool
    {
        return in_array($module, $this->enabledModules, true);
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->settings['features'] ?? [], true);
    }

    public function hasPermission(string $permission): bool
    {
        foreach ($this->permissions as $role => $perms) {
            if (in_array($permission, $perms, true)) {
                return true;
            }
        }

        return false;
    }

    public function toArray(): array
    {
        return [
            'business_type' => $this->businessType,
            'enabled_modules' => $this->enabledModules,
            'menus' => $this->menus,
            'permissions' => $this->permissions,
            'field_definitions' => $this->fieldDefinitions,
            'dashboard_widgets' => $this->dashboardWidgets,
            'pos_config' => $this->posConfig,
            'report_definitions' => $this->reportDefinitions,
            'workflows' => $this->workflows,
            'settings' => $this->settings,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            businessType: $data['business_type'],
            enabledModules: $data['enabled_modules'] ?? [],
            menus: $data['menus'] ?? [],
            permissions: $data['permissions'] ?? [],
            fieldDefinitions: $data['field_definitions'] ?? [],
            dashboardWidgets: $data['dashboard_widgets'] ?? [],
            posConfig: $data['pos_config'] ?? [],
            reportDefinitions: $data['report_definitions'] ?? [],
            workflows: $data['workflows'] ?? [],
            settings: $data['settings'] ?? [],
        );
    }
}
