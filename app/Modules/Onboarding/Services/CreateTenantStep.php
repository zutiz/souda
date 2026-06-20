<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Modules\Onboarding\Contracts\ProvisioningStep;
use App\Modules\Onboarding\Data\ProvisioningContext;

class CreateTenantStep implements ProvisioningStep
{
    public function handle(ProvisioningContext $context): void
    {
        // Tenant already created by CreateNewUser action
        // This step marks the onboarding as started
        $context->tenant->update([
            'onboarding_status' => 'provisioning',
            'onboarding_progress' => json_encode([]),
        ]);
    }

    public function rollback(ProvisioningContext $context): void
    {
        $context->tenant->update([
            'onboarding_status' => 'pending',
            'onboarding_progress' => null,
        ]);
    }

    public function label(): string
    {
        return 'Initializing workspace';
    }
}
