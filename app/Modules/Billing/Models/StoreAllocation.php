<?php

declare(strict_types=1);

namespace App\Modules\Billing\Models;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class StoreAllocation extends Model
{
    use CentralConnection;

    protected $table = 'billing_store_allocations';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'allocated_at' => 'datetime',
            'released_at' => 'datetime',
            'billing_start_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function scopeForTenant(Builder $query, string $tenantId): void
    {
        $query->where('tenant_id', $tenantId);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function release(): void
    {
        $this->update([
            'status' => 'released',
            'released_at' => now(),
        ]);
    }
}
