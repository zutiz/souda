<?php

namespace App\Console\Commands\Tenant;

use App\Jobs\MigrateTenantToDedicated;
use App\Jobs\MigrateTenantToShared;
use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Illuminate\Support\Facades\Log;

class MigrateTenantsCommand extends TenantCommand
{
    protected $signature = 'tenants:migrate-mode
        {--target= : Target mode (shared|dedicated)}
        {--plan= : Only migrate tenants on this plan slug (free|starter|professional|enterprise)}
        {--dry-run : Preview changes without executing}
        {--tenant= : Migrate a single tenant by ID}';

    protected $description = 'Migrate tenants between shared and dedicated modes';

    public function handle(): int
    {
        $target = $this->option('target');
        $planSlug = $this->option('plan');
        $tenantId = $this->option('tenant');
        $dryRun = $this->option('dry-run');

        if ($target && ! in_array($target, ['shared', 'dedicated'])) {
            $this->error('Target mode must be "shared" or "dedicated"');

            return self::FAILURE;
        }

        $query = Tenant::query();

        if ($tenantId) {
            $query->where('id', $tenantId);
        }

        if ($planSlug) {
            $query->whereHas('subscriptions.plan', fn ($q) => $q->where('slug', $planSlug));
        }

        $count = $query->count();
        $this->info("Found {$count} tenants to process.");

        if ($dryRun) {
            $query->cursor()->each(function (Tenant $tenant) {
                $current = $tenant->tenancy_mode;
                $this->line("  [DRY-RUN] Tenant {$tenant->id}: {$current}");
            });

            return self::SUCCESS;
        }

        $query->cursor()->each(function (Tenant $tenant) use ($target) {
            $this->processTenant($tenant, $target);
        });

        return self::SUCCESS;
    }

    protected function processTenant(Tenant $tenant, ?string $target): void
    {
        if ($target) {
            $this->migrateToTarget($tenant, $target);

            return;
        }

        $subscription = $tenant->activeSubscription();
        $planSlug = $subscription?->plan?->slug ?? 'free';

        /** @var TenantManager $manager */
        $manager = app(TenantManager::class);
        $targetMode = $manager->guessModeFromPlan($planSlug);

        if ($targetMode !== $tenant->tenancy_mode) {
            $this->migrateToTarget($tenant, $targetMode);
        }
    }

    protected function migrateToTarget(Tenant $tenant, string $target): void
    {
        if ($tenant->tenancy_mode === $target) {
            $this->line("  Tenant {$tenant->id} already in {$target} mode. Skipping.");

            return;
        }

        $this->info("Migrating tenant {$tenant->id}: {$tenant->tenancy_mode} -> {$target}");

        try {
            if ($target === 'dedicated') {
                $job = app(MigrateTenantToDedicated::class, ['tenantId' => $tenant->id]);
            } else {
                $job = app(MigrateTenantToShared::class, ['tenantId' => $tenant->id]);
            }

            $job->handle();
            $this->info('  Done.');
        } catch (\Throwable $e) {
            $this->error("  Failed: {$e->getMessage()}");
            Log::error('Tenant migration failed', [
                'tenant_id' => $tenant->id,
                'target' => $target,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
