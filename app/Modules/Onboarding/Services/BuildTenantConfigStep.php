<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Modules\BusinessType\Services\BusinessTypeEngine;
use App\Modules\Onboarding\Contracts\ProvisioningStep;
use App\Modules\Onboarding\Data\ProvisioningContext;

class BuildTenantConfigStep implements ProvisioningStep
{
    public function handle(ProvisioningContext $context): void
    {
        $engine = app(BusinessTypeEngine::class);
        $engine->rebuildConfig($context->tenant);
    }

    public function rollback(ProvisioningContext $context): void
    {
        $engine = app(BusinessTypeEngine::class);
        $engine->invalidateConfig($context->tenant);
    }

    public function label(): string
    {
        return 'Finalizing configuration';
    }
}
