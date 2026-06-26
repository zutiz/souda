<?php

namespace App\Tenancy\Models\Concerns;

use App\Models\Tenant;
use App\Tenancy\Scopes\TenantScope;
use App\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasTenantScope
{
    public static function bootHasTenantScope(): void
    {
        try {
            static::addGlobalScope(app(TenantScope::class));
        } catch (\Throwable) {
            // No app context available (e.g., unit tests without booted application)
        }

        static::creating(function ($model) {
            try {
                $manager = app(TenantManager::class);
                if ($manager->initialized() && $manager->isShared() && ! $model->tenant_id) {
                    $model->tenant_id = $manager->id();
                }
            } catch (\Throwable) {
                // No app context available
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
