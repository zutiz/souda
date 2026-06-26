<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Tenancy\TenantManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

abstract class TenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $tenantId;

    public function __construct()
    {
        $manager = app(TenantManager::class);

        $this->tenantId = $manager->initialized()
            ? $manager->id()
            : (tenancy()->initialized ? tenancy()->tenant->id : '');
    }

    /**
     * Initialize tenant context before execution.
     */
    public function handle(): void
    {
        $manager = app(TenantManager::class);

        if (! $manager->initialized() && $this->tenantId) {
            $tenant = Tenant::find($this->tenantId);

            if (! $tenant) {
                Log::warning('Tenant not found for job', [
                    'tenant_id' => $this->tenantId,
                    'job' => static::class,
                ]);

                $this->fail("Tenant not found: {$this->tenantId}");

                return;
            }

            $manager->initialize($tenant);
        }

        try {
            $this->execute();
        } finally {
            if ($manager->initialized()) {
                $manager->end();
            }
        }
    }

    abstract protected function execute(): void;

    public function failed(\Throwable $exception): void
    {
        Log::error('Tenant job failed', [
            'tenant_id' => $this->tenantId ?? null,
            'job' => static::class,
            'error' => $exception->getMessage(),
        ]);
    }
}
