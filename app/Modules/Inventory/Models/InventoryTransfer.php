<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Database\Factories\InventoryTransferFactory;
use App\Modules\Inventory\Enums\TransferStatusEnum;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryTransfer extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected $table = 'inventory_transfers';

    protected static function newFactory(): InventoryTransferFactory
    {
        return InventoryTransferFactory::new();
    }

    protected $fillable = [
        'reference',
        'from_warehouse_id',
        'to_warehouse_id',
        'status',
        'description',
        'created_by',
        'sent_at',
        'received_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransferStatusEnum::class,
            'sent_at' => 'datetime',
            'received_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryTransferItem::class, 'transfer_id');
    }
}
