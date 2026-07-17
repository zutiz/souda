<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Variant;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Database\Factories\PurchaseSuggestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseSuggestion extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected $table = 'inventory_purchase_suggestions';

    protected static function newFactory(): PurchaseSuggestionFactory
    {
        return PurchaseSuggestionFactory::new();
    }

    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'current_quantity',
        'reserved_quantity',
        'available_quantity',
        'reorder_level',
        'lead_time_days',
        'safety_stock',
        'sales_velocity',
        'suggested_quantity',
        'status',
        'notes',
        'order_reference',
    ];

    protected function casts(): array
    {
        return [
            'current_quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'available_quantity' => 'integer',
            'reorder_level' => 'integer',
            'lead_time_days' => 'integer',
            'safety_stock' => 'integer',
            'sales_velocity' => 'decimal:2',
            'suggested_quantity' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
}
