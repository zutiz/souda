<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Modules\Product\Traits\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    use Sluggable;

    protected $fillable = [
        'name',
        'slug',
        'frontend_type',
        'is_filterable',
        'is_required',
        'is_variant',
        'sort_order',
        'validation_rules',
    ];

    protected function casts(): array
    {
        return [
            'is_filterable' => 'boolean',
            'is_required' => 'boolean',
            'is_variant' => 'boolean',
            'validation_rules' => 'array',
        ];
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function productValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function scopeVariant($query)
    {
        return $query->where('is_variant', true);
    }

    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }
}
