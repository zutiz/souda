<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Models\TenantSetting;
use App\Modules\Onboarding\Contracts\ProvisioningStep;
use App\Modules\Onboarding\Data\ProvisioningContext;

class ConfigureProductSchemaStep implements ProvisioningStep
{
    public function handle(ProvisioningContext $context): void
    {
        $registry = app(TenantTemplateRegistry::class);
        $template = $registry->get($context->businessTypeSlug);

        $schema = $template?->productSchema() ?? [];

        if (empty($schema)) {
            return;
        }

        $setting = TenantSetting::query()
            ->where('tenant_id', $context->tenant->id)
            ->first();

        if ($setting === null) {
            return;
        }

        $extra = $setting->extra;
        $extra['product_schema'] = $schema;

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
        unset($extra['product_schema']);
        $setting->update(['extra' => $extra]);
    }

    public function label(): string
    {
        return 'Configuring product fields';
    }
}
