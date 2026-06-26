<?php

declare(strict_types=1);

namespace App\Modules\Product\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/** @mixin Model */
trait Sluggable
{
    public static function bootSluggable(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->slug) && ! empty($model->name)) {
                $model->slug = $model->generateSlug($model->name);
            }
        });
    }

    public function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;

        $counter = 1;

        while ($this->slugExists($slug)) {
            $slug = $original.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function slugExists(string $slug): bool
    {
        $table = $this->getTable();

        return static::query()
            ->where('slug', $slug)
            ->when($this->exists, fn ($q) => $q->where($this->getKeyName(), '!=', $this->getKey()))
            ->exists();
    }
}
