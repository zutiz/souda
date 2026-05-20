<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Modules\Product\Enums\StockReservationStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'variant_id',
        'quantity',
        'reference_type',
        'reference_id',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'status' => StockReservationStatusEnum::class,
            'quantity' => 'integer',
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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', StockReservationStatusEnum::Active)
            ->where('expires_at', '>', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', StockReservationStatusEnum::Active)
            ->where('expires_at', '<=', now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function markAsConsumed(): void
    {
        $this->update(['status' => StockReservationStatusEnum::Consumed]);
    }

    public function markAsCancelled(): void
    {
        $this->update(['status' => StockReservationStatusEnum::Cancelled]);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => StockReservationStatusEnum::Expired]);
    }
}
