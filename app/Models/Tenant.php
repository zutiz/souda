<?php

namespace App\Models;

use App\Modules\Billing\Models\Subscription;
use App\Modules\BusinessType\Models\BusinessType;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
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
 * @property string $tenancy_mode
 * @property string|null $database_name
 * @property int|null $business_type_id
 * @property string|null $logo
 * @property string $onboarding_status
 * @property array|null $onboarding_progress
 * @property Carbon|null $onboarded_at
 * @property array<string, mixed> $data
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    /** @use HasFactory<TenantFactory> */
    use HasDatabase, HasFactory, SoftDeletes;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'logo',
            'owner_id',
            'trial_ends_at',
            'trial_used',
            'tenancy_mode',
            'business_type_id',
            'onboarding_status',
            'onboarding_progress',
            'onboarded_at',
            'database_name',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'trial_used' => 'boolean',
            'onboarded_at' => 'datetime',
            'onboarding_progress' => 'array',
        ];
    }

    public function getDatabaseName(): string
    {
        return $this->database_name ?? 'souda_tenant_'.$this->id;
    }

    public function isShared(): bool
    {
        return $this->tenancy_mode === 'shared';
    }

    public function isDedicated(): bool
    {
        return $this->tenancy_mode === 'dedicated';
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot(['role', 'is_default'])
            ->withTimestamps();
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'tenant_id', 'id');
    }

    public function businessType(): BelongsTo
    {
        return $this->belongsTo(BusinessType::class, 'business_type_id');
    }

    public function tenantSetting(): HasOne
    {
        return $this->hasOne(TenantSetting::class, 'tenant_id');
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
