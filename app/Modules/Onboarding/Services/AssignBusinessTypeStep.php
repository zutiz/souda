<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Modules\BusinessType\Services\BusinessTypeEngine;
use App\Modules\Onboarding\Contracts\ProvisioningStep;
use App\Modules\Onboarding\Data\ProvisioningContext;

class AssignBusinessTypeStep implements ProvisioningStep
{
    public function handle(ProvisioningContext $context): void
    {
        $engine = app(BusinessTypeEngine::class);
        $engine->assignBusinessType($context->tenant, $context->businessTypeSlug);
    }

    public function rollback(ProvisioningContext $context): void
    {
        $context->tenant->update(['business_type_id' => null]);
    }

    public function label(): string
    {
        return 'Assigning business type';
    }
}
