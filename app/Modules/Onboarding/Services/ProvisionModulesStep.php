<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Modules\BusinessType\Models\TenantModuleOverride;
use App\Modules\BusinessType\Services\IndustryPackRegistry;
use App\Modules\Onboarding\Contracts\ProvisioningStep;
use App\Modules\Onboarding\Data\ProvisioningContext;

class ProvisionModulesStep implements ProvisioningStep
{
    public function handle(ProvisioningContext $context): void
    {
        $registry = app(IndustryPackRegistry::class);
        $pack = $registry->get($context->businessTypeSlug);

        if ($pack === null) {
            return;
        }

        foreach ($pack->modules() as $key => $value) {
            $slug = is_string($key) ? $key : $value;
            $config = is_array($value) ? ($value['config_defaults'] ?? []) : [];

            TenantModuleOverride::query()->updateOrCreate(
                [
                    'tenant_id' => $context->tenant->id,
                    'module_slug' => $slug,
                ],
                [
                    'is_enabled' => true,
                    'settings' => $config,
                ],
            );
        }
    }

    public function rollback(ProvisioningContext $context): void
    {
        TenantModuleOverride::query()
            ->where('tenant_id', $context->tenant->id)
            ->delete();
    }

    public function label(): string
    {
        return 'Enabling modules';
    }
}
