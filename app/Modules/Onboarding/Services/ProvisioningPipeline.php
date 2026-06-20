<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Models\Tenant;
use App\Modules\Onboarding\Contracts\ProvisioningStep;
use App\Modules\Onboarding\Data\ProvisioningContext;
use App\Modules\Onboarding\Events\OnboardingCompleted;
use App\Modules\Onboarding\Events\OnboardingFailed;
use App\Modules\Onboarding\Events\OnboardingStarted;
use App\Modules\Onboarding\Events\OnboardingStepCompleted;
use Illuminate\Support\Facades\Log;

class ProvisioningPipeline
{
    private array $steps = [];

    public function __construct(
        private CreateTenantStep $createTenant,
        private AssignBusinessTypeStep $assignType,
        private ProvisionModulesStep $provisionModules,
        private CreatePermissionsStep $createPermissions,
        private SeedDefaultDataStep $seedData,
        private ConfigureProductSchemaStep $configureSchema,
        private ConfigureDashboardStep $configureDashboard,
        private ConfigurePOSStep $configurePOS,
        private CreateDefaultTeamStep $createTeam,
        private BuildTenantConfigStep $buildConfig,
    ) {
        $this->steps = [
            $this->createTenant,
            $this->assignType,
            $this->provisionModules,
            $this->createPermissions,
            $this->seedData,
            $this->configureSchema,
            $this->configureDashboard,
            $this->configurePOS,
            $this->createTeam,
            $this->buildConfig,
        ];
    }

    public function run(Tenant $tenant, string $businessTypeSlug, array $planData = []): void
    {
        $context = new ProvisioningContext(
            tenant: $tenant,
            businessTypeSlug: $businessTypeSlug,
            planData: $planData,
        );

        OnboardingStarted::dispatch($tenant, $businessTypeSlug);

        $completedSteps = [];

        foreach ($this->steps as $index => $step) {
            try {
                $context->setCurrentStep($step::class);

                $step->handle($context);

                $completedSteps[] = $step;

                $this->updateProgress($tenant, $step::class, 'completed', $index);

                OnboardingStepCompleted::dispatch(
                    $tenant,
                    $step::class,
                    $index,
                    $step->label(),
                );
            } catch (\Throwable $e) {
                $this->handleFailure($tenant, $step, $completedSteps, $context, $e);

                throw $e;
            }
        }

        $tenant->update([
            'onboarding_status' => 'completed',
            'onboarded_at' => now(),
        ]);

        OnboardingCompleted::dispatch($tenant);
    }

    public function resumeFrom(Tenant $tenant, string $stepClass, array $planData = []): void
    {
        $context = new ProvisioningContext(
            tenant: $tenant,
            businessTypeSlug: $tenant->businessType?->slug ?? 'general',
            planData: $planData,
        );

        $resumeIndex = null;
        foreach ($this->steps as $index => $step) {
            if ($step::class === $stepClass) {
                $resumeIndex = $index;
                break;
            }
        }

        if ($resumeIndex === null) {
            throw new \RuntimeException("Step [{$stepClass}] not found in pipeline.");
        }

        $completedSteps = [];

        for ($i = $resumeIndex; $i < count($this->steps); $i++) {
            $step = $this->steps[$i];

            try {
                $context->setCurrentStep($step::class);
                $step->handle($context);
                $completedSteps[] = $step;
                $this->updateProgress($tenant, $step::class, 'completed', $i);
                OnboardingStepCompleted::dispatch($tenant, $step::class, $i, $step->label());
            } catch (\Throwable $e) {
                $this->handleFailure($tenant, $step, $completedSteps, $context, $e);
                throw $e;
            }
        }

        $tenant->update([
            'onboarding_status' => 'completed',
            'onboarded_at' => now(),
        ]);

        OnboardingCompleted::dispatch($tenant);
    }

    private function updateProgress(Tenant $tenant, string $stepClass, string $status, int $stepIndex): void
    {
        $progress = json_decode($tenant->onboarding_progress, true) ?? [];
        $progress[] = [
            'step' => $stepClass,
            'status' => $status,
            'index' => $stepIndex,
            'timestamp' => now()->toIso8601String(),
        ];

        $tenant->updateQuietly([
            'onboarding_progress' => json_encode($progress),
        ]);
    }

    private function handleFailure(
        Tenant $tenant,
        ProvisioningStep $failedStep,
        array $completedSteps,
        ProvisioningContext $context,
        \Throwable $error,
    ): void {
        Log::error('Onboarding provisioning failed, rolling back', [
            'tenant_id' => $tenant->id,
            'failed_step' => $failedStep::class,
            'error' => $error->getMessage(),
        ]);

        foreach (array_reverse($completedSteps) as $step) {
            try {
                $step->rollback($context);
            } catch (\Throwable $rollbackError) {
                Log::error('Rollback step failed', [
                    'step' => $step::class,
                    'error' => $rollbackError->getMessage(),
                ]);
            }
        }

        $tenant->update([
            'onboarding_status' => 'failed',
            'onboarding_progress' => json_encode([
                'failed_step' => $failedStep::class,
                'error' => $error->getMessage(),
                'completed_steps' => array_map(fn ($s) => $s::class, $completedSteps),
            ]),
        ]);

        OnboardingFailed::dispatch($tenant, $failedStep::class, $error->getMessage());
    }
}
