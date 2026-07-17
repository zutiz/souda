<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Console\Commands;

use App\Modules\Inventory\Models\InventoryBalance;
use Illuminate\Console\Command;

class DeadStockReportCommand extends Command
{
    protected $signature = 'inventory:dead-stock-report
        {--days=90 : Number of days without movement to consider dead stock}
        {--warehouse= : Filter by warehouse ID}';

    protected $description = 'Find products with no stock movement in the specified number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $warehouseId = $this->option('warehouse') ? (int) $this->option('warehouse') : null;

        $cutoff = now()->subDays($days);

        $query = InventoryBalance::where('quantity', '>', 0)
            ->where(function ($q) use ($cutoff) {
                $q->whereNull('last_movement_at')
                    ->orWhere('last_movement_at', '<', $cutoff);
            });

        if ($warehouseId !== null) {
            $query->where('warehouse_id', $warehouseId);
        }

        $deadStock = $query->get();

        if ($deadStock->isEmpty()) {
            $this->info('No dead stock found.');

            return 0;
        }

        $this->line("Found {$deadStock->count()} dead stock item(s) (no movement in {$days} days).");

        $this->table(
            ['Product ID', 'Warehouse', 'Quantity', 'Last Movement', 'Value'],
            $deadStock->map(fn (InventoryBalance $b) => [
                $b->product_id,
                $b->warehouse_id,
                $b->quantity,
                $b->last_movement_at?->format('Y-m-d H:i') ?? 'Never',
                $b->total_stock_value,
            ]),
        );

        return 0;
    }
}
