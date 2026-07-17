<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Variant;
use App\Modules\Product\Models\Warehouse;
use App\Modules\Product\Traits\HasOptimisticLocking;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    use HasOptimisticLocking;
    use HasTenantScope;

    protected $table = 'inventory_balances';

    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'average_unit_cost',
        'total_stock_value',
        'lock_version',
        'last_movement_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'available_quantity' => 'integer',
            'average_unit_cost' => 'integer',
            'total_stock_value' => 'integer',
            'lock_version' => 'integer',
            'last_movement_at' => 'datetime',
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

    public function getAvailableQuantity(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }
}
