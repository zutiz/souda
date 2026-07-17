<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Modules\Inventory\Models\StockReservation;
use App\Modules\Inventory\Services\ReservationEngine;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireStockReservations extends Command
{
    protected $signature = 'inventory:expire-reservations
        {--dry-run : Preview expirations without making changes}';

    protected $description = 'Expire stock reservations that have passed their TTL';

    public function handle(ReservationEngine $reservationEngine): int
    {
        $dryRun = $this->option('dry-run');
        $totalExpired = 0;

        Tenant::all()->each(function (Tenant $tenant) use ($reservationEngine, $dryRun, &$totalExpired) {
            tenancy()->initialize($tenant);

            if ($dryRun) {
                $count = StockReservation::where('status', 'active')
                    ->where('expires_at', '<=', now())
                    ->count();

                if ($count > 0) {
                    $this->line("  [DRY-RUN] Tenant {$tenant->id}: {$count} reservations would expire");
                }
            } else {
                $expired = $reservationEngine->expireOldReservations();

                if ($expired > 0) {
                    $this->info("Tenant {$tenant->id}: expired {$expired} reservations");
                }

                $totalExpired += $expired;
            }

            tenancy()->end();
        });

        $this->newLine();
        $this->table(
            ['Metric', 'Value'],
            [
                ['Mode', $dryRun ? 'Dry Run' : 'Live'],
                ['Total expired', $dryRun ? 'N/A (dry run)' : (string) $totalExpired],
            ],
        );

        Log::info('ExpireStockReservations completed', [
            'mode' => $dryRun ? 'dry-run' : 'live',
            'expired_count' => $totalExpired,
        ]);

        return Command::SUCCESS;
    }
}
