<?php

namespace App\Modules\Billing\Models;

use App\Models\Tenant;
use App\Modules\Billing\Enums\BillingCycle;
use App\Modules\Billing\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Subscription extends Model
{
    use CentralConnection;

    protected $table = 'billing_subscriptions';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'billing_cycle' => BillingCycle::class,
            'amount' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'next_billing_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * The plan this subscription belongs to.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * The tenant this subscription belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Payments made for this subscription.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'subscription_id');
    }

    /**
     * Scope subscriptions for a specific tenant.
     */
    public function scopeForTenant(Builder $query, string $tenantId): void
    {
        $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope to active subscriptions (including grace and trial).
     */
    public function scopeAccessible(Builder $query): void
    {
        $query->whereIn('status', [
            SubscriptionStatus::Trial,
            SubscriptionStatus::Active,
            SubscriptionStatus::Grace,
        ]);
    }

    /**
     * Scope to currently active (non-expired) subscriptions.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', SubscriptionStatus::Active);
    }

    /**
     * Scope subscriptions with pending payment.
     */
    public function scopePendingPayment(Builder $query): void
    {
        $query->where('status', SubscriptionStatus::PendingPayment);
    }

    /**
     * Check if the subscription is currently accessible.
     */
    public function isAccessible(): bool
    {
        return $this->status->isAccessible();
    }

    /**
     * Check if the subscription requires payment action.
     */
    public function requiresPayment(): bool
    {
        return $this->status->requiresPayment();
    }

    /**
     * Check if the subscription is currently on trial.
     */
    public function onTrial(): bool
    {
        return $this->status === SubscriptionStatus::Trial
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Mark this subscription as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => SubscriptionStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
