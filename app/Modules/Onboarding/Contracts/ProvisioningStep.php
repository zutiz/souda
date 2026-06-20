<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Contracts;

use App\Modules\Onboarding\Data\ProvisioningContext;

interface ProvisioningStep
{
    public function handle(ProvisioningContext $context): void;

    public function rollback(ProvisioningContext $context): void;

    public function label(): string;
}
