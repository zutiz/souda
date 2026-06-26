<?php

namespace App\Tenancy\Scopes;

use App\Tenancy\TenantManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        try {
            $manager = app(TenantManager::class);

            if ($manager->initialized() && $manager->isShared()) {
                $builder->where($model->getTable().'.tenant_id', $manager->id());
            }
        } catch (\Throwable) {
            // No app context available
        }
    }

    public function extend(Builder $builder): void
    {
        $builder->macro('withoutTenancy', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}
