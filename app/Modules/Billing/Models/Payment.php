<?php

namespace App\Modules\Billing\Models;

use App\Modules\Billing\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Payment extends Model
{
    use CentralConnection;

    protected $table = 'billing_payments';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => PaymentStatus::class,
            'payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    public function scopeForTenant(Builder $query, string $tenantId): void
    {
        $query->where('tenant_id', $tenantId);
    }

    public function scopeForGateway(Builder $query, string $gateway): void
    {
        $query->where('gateway', $gateway);
    }

    /**
     * Mark payment as completed.
     */
    public function markAsCompleted(?string $transactionId = null): void
    {
        $this->update([
            'status' => PaymentStatus::Completed,
            'paid_at' => now(),
            'transaction_id' => $transactionId ?? $this->transaction_id,
        ]);
    }

    /**
     * Mark payment as failed.
     */
    public function markAsFailed(?string $message = null): void
    {
        $payload = $this->payload;
        $payload['error'] = $message;

        $this->update([
            'status' => PaymentStatus::Failed,
            'payload' => $payload,
        ]);
    }
}
