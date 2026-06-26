<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Data;

use App\Models\Tenant;

readonly class ProvisioningContext
{
    public string $currentStep = '';

    public function __construct(
        public Tenant $tenant,
        public string $businessTypeSlug,
        public array $planData = [],
    ) {}

    public function setCurrentStep(string $step): void
    {
        $this->currentStep = $step;
    }
}
