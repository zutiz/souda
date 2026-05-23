<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Modules\Product\Database\Factories\CategoryFactory;
use App\Modules\Product\Traits\HasMaterializedPath;
use App\Modules\Product\Traits\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    use HasMaterializedPath;
    use Sluggable;

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image_path',
        'is_active',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product');
    }

    public function allProducts(): BelongsToMany
    {
        $ids = $this->getDescendantIds();
        $ids[] = $this->id;

        return $this->belongsToMany(Product::class, 'category_product')
            ->whereIn('category_id', $ids);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class, 'scope_id')
            ->where('scope', 'category');
    }
}
