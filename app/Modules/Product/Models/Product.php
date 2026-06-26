<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Modules\Product\Database\Factories\ProductFactory;
use App\Modules\Product\Enums\ProductStatusEnum;
use App\Modules\Product\Enums\ProductTypeEnum;
use App\Modules\Product\Traits\HasProductMedia;
use App\Modules\Product\Traits\HasProductStock;
use App\Modules\Product\Traits\Sluggable;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory;
    use HasProductMedia;
    use HasProductStock;
    use HasTenantScope;
    use HasUlids;
    use Searchable;
    use Sluggable;

    protected $fillable = [
        'category_id',
        'brand_id',
        'tax_category_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'barcode_type',
        'description',
        'short_description',
        'type',
        'status',
        'base_price',
        'compare_at_price',
        'cost_price',
        'tax_inclusive',
        'track_inventory',
        'low_stock_threshold',
        'total_quantity',
        'total_reserved',
        'warehouse_count',
        'weight',
        'length',
        'width',
        'height',
        'metadata',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tax_inclusive' => 'boolean',
            'track_inventory' => 'boolean',
            'metadata' => 'array',
            'published_at' => 'datetime',
            'type' => ProductTypeEnum::class,
            'status' => ProductStatusEnum::class,
        ];
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function taxCategory(): BelongsTo
    {
        return $this->belongsTo(TaxCategory::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class);
    }

    public function warehouseStock(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class, 'scope_id')
            ->where('scope', 'product');
    }

    public function defaultVariant(): ?Variant
    {
        return $this->variants()->where('is_default', true)->first();
    }

    public function searchableAs(): string
    {
        return 'products';
    }

    public function toSearchableArray(): array
    {
        return [
            'objectID' => (string) $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'base_price' => $this->base_price,
            'status' => $this->status?->value,
            'type' => $this->type?->value,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'category_name' => $this->category?->name,
            'brand_name' => $this->brand?->name,
            'total_stock' => $this->total_available,
            'created_at' => $this->created_at?->timestamp,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === ProductStatusEnum::Active
            && $this->published_at !== null;
    }

    public function scopeActive($query)
    {
        return $query->where('status', ProductStatusEnum::Active);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', ProductStatusEnum::Draft);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
