<?php

namespace App\Modules\Billing\Jobs;

use App\Modules\Billing\Services\SeatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncSeatAllocations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $tenantId,
    ) {}

    public function handle(SeatService $seatService): void
    {
        try {
            $synced = $seatService->refreshSeatAllocationsFromUsers($this->tenantId);

            if ($synced > 0) {
                Log::info('Seat allocations synced from users', [
                    'tenant_id' => $this->tenantId,
                    'synced' => $synced,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to sync seat allocations', [
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage(),
            ]);

            $this->fail($e);
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SyncSeatAllocations job failed permanently', [
            'tenant_id' => $this->tenantId,
            'error' => $e->getMessage(),
        ]);
    }
}
