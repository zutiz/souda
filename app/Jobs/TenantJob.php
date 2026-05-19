<?php

namespace App\Jobs;

use App\Models\Tenant;
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
        $this->tenantId = tenancy()->initialized
            ? tenancy()->tenant->id
            : '';
    }

    /**
     * Initialize tenancy before execution.
     * QueueTenancyBootstrapper handles this automatically in most cases,
     * but this provides an explicit fallback.
     */
    public function handle(): void
    {
        if (! tenancy()->initialized && $this->tenantId) {
            $tenant = Tenant::find($this->tenantId);

            if (! $tenant) {
                Log::warning('Tenant not found for job', [
                    'tenant_id' => $this->tenantId,
                    'job' => static::class,
                ]);

                $this->fail("Tenant not found: {$this->tenantId}");

                return;
            }

            tenancy()->initialize($tenant);
        }

        try {
            $this->execute();
        } finally {
            if (tenancy()->initialized) {
                tenancy()->end();
            }
        }
    }

    /**
     * Implement job logic here. Tenant context is already initialized.
     */
    abstract protected function execute(): void;

    /**
     * Override to add tenant-aware context to failed jobs.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Tenant job failed', [
            'tenant_id' => $this->tenantId ?? null,
            'job' => static::class,
            'error' => $exception->getMessage(),
        ]);
    }
}
