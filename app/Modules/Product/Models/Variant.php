<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Modules\Product\Traits\HasProductStock;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Variant extends Model
{
    use HasProductStock;
    use HasUlids;

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'barcode_type',
        'name',
        'price',
        'compare_at_price',
        'cost_price',
        'track_inventory',
        'low_stock_threshold',
        'weight',
        'length',
        'width',
        'height',
        'is_default',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'track_inventory' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(AttributeValue::class, 'variant_attribute_values');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function warehouseStock(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
