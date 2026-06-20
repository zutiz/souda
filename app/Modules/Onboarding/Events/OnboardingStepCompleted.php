<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Events;

use App\Models\Tenant;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OnboardingStepCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public string $stepClass,
        public int $stepIndex,
        public string $stepLabel,
    ) {}
}
