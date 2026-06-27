<?php

declare(strict_types=1);

namespace App\Modules\Store\Console\Commands;

use App\Models\Tenant;
use App\Modules\Store\Models\Store;
use App\Modules\Store\Services\StoreService;
use Illuminate\Console\Command;

class StoresMigrateExistingCommand extends Command
{
    protected $signature = 'stores:migrate-existing
        {--dry-run : Preview changes without creating stores}
        {--tenant= : Only migrate a specific tenant by ID}';

    protected $description = 'Create default stores for existing tenants that have none';

    public function handle(StoreService $storeService): int
    {
        $tenants = Tenant::query()
            ->when($this->option('tenant'), fn ($q, $id) => $q->where('id', $id))
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            $count = Store::query()->count();

            if ($count > 0) {
                $this->warn("[{$tenant->id}] Skipped — already has {$count} store(s)");
                $skipped++;
                tenancy()->end();

                continue;
            }

            $name = $this->option('dry-run')
                ? 'Main Store'
                : $this->ask("[{$tenant->id}] Enter store name", 'Main Store');

            if ($this->option('dry-run')) {
                $this->line("[{$tenant->id}] Would create default store: {$name}");
                $created++;
                tenancy()->end();

                continue;
            }

            $storeService->createStore([
                'name' => $name,
                'slug' => 'main',
                'code' => 'STORE-001',
                'currency' => 'XOF',
                'timezone' => 'Africa/Porto-Novo',
                'is_default' => true,
                'status' => 'active',
            ]);

            $this->info("[{$tenant->id}] Created default store: {$name}");
            $created++;

            tenancy()->end();
        }

        $this->newLine();
        $this->table(
            ['', 'Count'],
            [
                ['Processed', $tenants->count()],
                ['Created/Scheduled', $created],
                ['Skipped', $skipped],
            ]
        );

        return self::SUCCESS;
    }
}
