<?php

namespace App\Modules\Billing\Models;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Billing\Enums\SeatStatus;
use App\Modules\Billing\Enums\SeatType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class SeatAllocation extends Model
{
    use CentralConnection;

    protected $table = 'billing_seat_allocations';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'seat_type' => SeatType::class,
            'status' => SeatStatus::class,
            'allocated_at' => 'datetime',
            'released_at' => 'datetime',
            'billing_start_at' => 'datetime',
            'metadata' => 'array',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeForTenant(Builder $query, string $tenantId): void
    {
        $query->where('tenant_id', $tenantId);
    }

    public function scopeByStatus(Builder $query, SeatStatus $status): void
    {
        $query->where('status', $status);
    }

    public function scopeByType(Builder $query, SeatType $type): void
    {
        $query->where('seat_type', $type);
    }

    public function scopeConsumed(Builder $query): void
    {
        $query->whereIn('status', [SeatStatus::Active, SeatStatus::Pending]);
    }

    public function isConsumed(): bool
    {
        return $this->status->isConsumed();
    }

    public function release(): void
    {
        $this->update([
            'status' => SeatStatus::Released,
            'released_at' => now(),
        ]);
    }
}
