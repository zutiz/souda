<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\ReservationStatusEnum;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Variant;
use App\Modules\Product\Models\Warehouse;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Database\Factories\StockReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected static function newFactory(): StockReservationFactory
    {
        return StockReservationFactory::new();
    }

    protected $table = 'inventory_stock_reservations';

    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'quantity',
        'reference',
        'reference_type',
        'expires_at',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
            'status' => ReservationStatusEnum::class,
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

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function markAsConsumed(): void
    {
        $this->update(['status' => ReservationStatusEnum::Consumed, 'consumed_at' => now()]);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => ReservationStatusEnum::Cancelled]);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => ReservationStatusEnum::Expired]);
    }
}
