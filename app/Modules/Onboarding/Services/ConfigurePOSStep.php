<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Models\TenantSetting;
use App\Modules\BusinessType\Services\IndustryPackRegistry;
use App\Modules\Onboarding\Contracts\ProvisioningStep;
use App\Modules\Onboarding\Data\ProvisioningContext;

class ConfigurePOSStep implements ProvisioningStep
{
    public function handle(ProvisioningContext $context): void
    {
        $registry = app(IndustryPackRegistry::class);
        $pack = $registry->get($context->businessTypeSlug);

        if ($pack === null) {
            return;
        }

        $posConfig = $pack->posConfig();

        if (empty($posConfig)) {
            return;
        }

        $setting = TenantSetting::query()
            ->where('tenant_id', $context->tenant->id)
            ->first();

        if ($setting === null) {
            return;
        }

        $extra = $setting->extra;
        $extra['pos_config'] = $posConfig;

        $setting->update(['extra' => $extra]);
    }

    public function rollback(ProvisioningContext $context): void
    {
        $setting = TenantSetting::query()
            ->where('tenant_id', $context->tenant->id)
            ->first();

        if ($setting === null) {
            return;
        }

        $extra = $setting->extra;
        unset($extra['pos_config']);
        $setting->update(['extra' => $extra]);
    }

    public function label(): string
    {
        return 'Configuring point of sale';
    }
}
