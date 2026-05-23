<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Modules\Product\Database\Factories\BrandFactory;
use App\Modules\Product\Traits\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory;
    use Sluggable;

    protected static function newFactory(): BrandFactory
    {
        return BrandFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo_path',
        'website_url',
        'is_active',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class, 'scope_id')
            ->where('scope', 'brand');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
