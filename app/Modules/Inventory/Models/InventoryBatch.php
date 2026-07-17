<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Database\Factories\InventoryBatchFactory;
use App\Modules\Inventory\Enums\BatchStatusEnum;
use App\Modules\Product\Models\Product;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryBatch extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected $table = 'inventory_batches';

    protected static function newFactory(): InventoryBatchFactory
    {
        return InventoryBatchFactory::new();
    }

    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'batch_number',
        'supplier_batch',
        'manufacturing_date',
        'expiry_date',
        'best_before',
        'initial_quantity',
        'remaining_quantity',
        'unit_cost',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'initial_quantity' => 'integer',
            'remaining_quantity' => 'integer',
            'unit_cost' => 'integer',
            'manufacturing_date' => 'date',
            'expiry_date' => 'date',
            'best_before' => 'date',
            'status' => BatchStatusEnum::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class, 'batch_id');
    }

    public function isExpired(): bool
    {
        return $this->expiry_date !== null && Carbon::parse($this->expiry_date)->isPast();
    }

    public function isExpiringSoon(int $withinDays = 30): bool
    {
        if ($this->expiry_date === null) {
            return false;
        }

        return Carbon::parse($this->expiry_date)->isFuture()
            && Carbon::parse($this->expiry_date)->diffInDays(now()) <= $withinDays;
    }

    public function markAsDepleted(): void
    {
        $this->update(['status' => BatchStatusEnum::Depleted]);
    }

    public function markAsQuarantined(): void
    {
        $this->update(['status' => BatchStatusEnum::Quarantined]);
    }

    public function markAsExpired(): void
    {
        $this->update(['status' => BatchStatusEnum::Expired]);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiring($query, int $withinDays = 30)
    {
        return $query->active()
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays($withinDays));
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }
}
