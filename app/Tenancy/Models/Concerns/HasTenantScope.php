<?php

namespace App\Tenancy\Models\Concerns;

use App\Models\Tenant;
use App\Tenancy\Scopes\TenantScope;
use App\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

trait HasTenantScope
{
    public static function bootHasTenantScope(): void
    {
        static::addGlobalScope(App::make(TenantScope::class));

        static::creating(function ($model) {
            $manager = App::make(TenantManager::class);

            if ($manager->initialized() && $manager->isShared() && ! $model->tenant_id) {
                $model->tenant_id = $manager->id();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
