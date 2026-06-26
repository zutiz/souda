<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Modules\Product\Traits\HasOptimisticLocking;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseStock extends Model
{
    use HasOptimisticLocking;
    use HasTenantScope;

    protected $table = 'warehouse_stock';

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'variant_id',
        'quantity',
        'reserved_quantity',
        'reorder_level',
        'lock_version',
        'last_movement_at',
    ];

    protected function casts(): array
    {
        return [
            'last_movement_at' => 'datetime',
            'quantity' => 'integer',
            'reserved_quantity' => 'integer',
            'reorder_level' => 'integer',
            'lock_version' => 'integer',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    public function getAvailableQuantity(): int
    {
        return $this->quantity - $this->reserved_quantity;
    }

    public function isLowStock(): bool
    {
        return $this->getAvailableQuantity() <= $this->reorder_level;
    }
}
