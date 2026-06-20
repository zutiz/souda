<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Modules\Onboarding\Contracts\ProvisioningStep;
use App\Modules\Onboarding\Data\ProvisioningContext;

class CreateDefaultTeamStep implements ProvisioningStep
{
    public function handle(ProvisioningContext $context): void
    {
        // Owner already created during registration and assigned admin role.
        // Future: seed default team members if plan allows.
    }

    public function rollback(ProvisioningContext $context): void {}

    public function label(): string
    {
        return 'Setting up team';
    }
}
