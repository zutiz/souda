<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Console\Commands;

use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Inventory\Services\CountEngine;
use Illuminate\Console\Command;

class CycleCountCommand extends Command
{
    protected $signature = 'inventory:cycle-count
        {--warehouse= : Warehouse ID to count}
        {--limit=50 : Maximum products per cycle count}';

    protected $description = 'Generate a cycle count for a random subset of tracked products';

    public function handle(CountEngine $countEngine): int
    {
        $warehouseId = $this->option('warehouse') ? (int) $this->option('warehouse') : null;
        $limit = (int) $this->option('limit');

        $warehouses = Warehouse::active()
            ->when($warehouseId, fn ($q) => $q->where('id', $warehouseId))
            ->get();

        if ($warehouses->isEmpty()) {
            $this->error('No active warehouses found.');

            return 1;
        }

        $total = 0;

        foreach ($warehouses as $warehouse) {
            $count = $countEngine->createCount(
                warehouseId: $warehouse->id,
                type: 'cycle',
            );

            $total += $count->items()->count();

            $this->line("Created cycle count {$count->reference} for warehouse '{$warehouse->name}' with {$count->items()->count()} item(s).");
        }

        $this->info("Cycle count complete: {$total} item(s) across {$warehouses->count()} warehouse(s).");

        return 0;
    }
}
