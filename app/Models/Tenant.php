<?php

namespace App\Models;

use App\Modules\Billing\Models\Subscription;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Database\TenantCollection;

/**
 * @property string $id
 * @property string|null $name
 * @property int|null $owner_id
 * @property Carbon|null $trial_ends_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property array<string, mixed> $data
 */
class Tenant extends BaseTenant
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory, SoftDeletes;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'owner_id',
            'trial_ends_at',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'tenant_id', 'id');
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->accessible()
            ->latest('id')
            ->first();
    }

    public function newCollection(array $models = []): TenantCollection
    {
        return new TenantCollection($models);
    }
}
