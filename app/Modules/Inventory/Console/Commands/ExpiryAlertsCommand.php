<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Console\Commands;

use App\Modules\Inventory\Events\BatchExpiring;
use App\Modules\Inventory\Models\InventoryBatch;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class ExpiryAlertsCommand extends Command
{
    protected $signature = 'inventory:expiry-alerts
        {--days= : Days within which to check for expiry}
        {--dry-run : List batches without dispatching events}';

    protected $description = 'Find batches expiring within the alert threshold and dispatch notifications';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? config('inventory.expiry_alert_days', 30));
        $dryRun = (bool) $this->option('dry-run');

        $expiring = InventoryBatch::expiring($days)->get();

        if ($expiring->isEmpty()) {
            $this->info('No batches expiring within '.$days.' days.');

            return 0;
        }

        $this->line("Found {$expiring->count()} batch(es) expiring within {$days} days.");

        if ($dryRun) {
            $this->table(
                ['ID', 'Batch', 'Product', 'Expiry', 'Remaining'],
                $expiring->map(fn (InventoryBatch $b) => [
                    $b->id, $b->batch_number, $b->product_id,
                    $b->expiry_date?->format('Y-m-d'), $b->remaining_quantity,
                ]),
            );

            return 0;
        }

        $count = 0;

        $expiring->chunk(100)->each(function (Collection $chunk) use (&$count) {
            foreach ($chunk as $batch) {
                event(new BatchExpiring(
                    batchId: $batch->id,
                    productId: $batch->product_id,
                    expiryDate: CarbonImmutable::parse($batch->expiry_date),
                    daysRemaining: (int) now()->diffInDays($batch->expiry_date, absolute: true),
                ));

                $count++;
            }
        });

        $this->info("Dispatched {$count} expiry alert(s).");

        return 0;
    }
}
