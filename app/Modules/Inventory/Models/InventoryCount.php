<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Models\User;
use App\Modules\Inventory\Enums\CountStatusEnum;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Database\Factories\InventoryCountFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryCount extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected $table = 'inventory_counts';

    protected static function newFactory(): InventoryCountFactory
    {
        return InventoryCountFactory::new();
    }

    protected $fillable = [
        'warehouse_id',
        'reference',
        'type',
        'status',
        'counted_by',
        'verified_by',
        'notes',
        'counted_at',
        'verified_at',
        'adjusted_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CountStatusEnum::class,
            'counted_at' => 'datetime',
            'verified_at' => 'datetime',
            'adjusted_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryCountItem::class, 'count_id');
    }

    public function countedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForWarehouse($query, int $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }
}
