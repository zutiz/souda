<?php

namespace App\Console\Commands\Tenant;

use App\Models\Tenant;
use Illuminate\Console\Command;

class ListTenantModesCommand extends Command
{
    protected $signature = 'tenants:list-modes
        {--mode= : Filter by mode (shared|dedicated)}
        {--plan= : Filter by plan slug}';

    protected $description = 'List all tenants and their tenancy modes';

    public function handle(): int
    {
        $query = Tenant::query()
            ->select(['id', 'name', 'tenancy_mode', 'database_name', 'created_at'])
            ->with('subscriptions.plan');

        if ($this->option('mode')) {
            $query->where('tenancy_mode', $this->option('mode'));
        }

        if ($this->option('plan')) {
            $query->whereHas('subscriptions.plan', fn ($q) => $q->where('slug', $this->option('plan')));
        }

        $tenants = $query->get()->map(function (Tenant $tenant) {
            $subscription = $tenant->activeSubscription();

            return [
                $tenant->id,
                $tenant->name ?? 'N/A',
                $tenant->tenancy_mode,
                $tenant->database_name ?? '(auto)',
                $subscription?->plan?->slug ?? 'none',
                $tenant->created_at->toDateString(),
            ];
        });

        $this->table(
            ['ID', 'Name', 'Mode', 'Database', 'Plan', 'Created'],
            $tenants,
        );

        $counts = Tenant::query()
            ->selectRaw('tenancy_mode, count(*) as count')
            ->groupBy('tenancy_mode')
            ->pluck('count', 'tenancy_mode');

        $this->newLine();
        $this->line("Shared: {$counts->get('shared', 0)} | Dedicated: {$counts->get('dedicated', 0)} | Total: ".Tenant::count());

        return self::SUCCESS;
    }
}
