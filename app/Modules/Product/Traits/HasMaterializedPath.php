<?php

declare(strict_types=1);

namespace App\Modules\Product\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** @mixin Model */
trait HasMaterializedPath
{
    public static function bootHasMaterializedPath(): void
    {
        static::saving(function (Model $model) {
            if ($model->isDirty('parent_id')) {
                $model->rebuildPath();
            }
        });
    }

    public function rebuildPath(): void
    {
        $path = '/';

        if ($this->parent_id !== null) {
            $parent = static::query()->find($this->parent_id);

            if ($parent !== null) {
                $path = $parent->materialized_path.$this->parent_id.'/';
                $this->depth = $parent->depth + 1;
            }
        } else {
            $this->depth = 0;
        }

        $this->materialized_path = $path;
    }

    public function getDescendants(): Collection
    {
        $searchPath = $this->materialized_path.$this->getKey().'/';

        return static::query()
            ->where('materialized_path', 'like', $searchPath.'%')
            ->orWhere('materialized_path', '=', $searchPath)
            ->get();
    }

    public function getDescendantIds(): array
    {
        return $this->getDescendants()->pluck('id')->toArray();
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }
}
