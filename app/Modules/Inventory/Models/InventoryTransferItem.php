<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransferItem extends Model
{
    use HasTenantScope;

    protected $table = 'inventory_transfer_items';

    protected $fillable = [
        'transfer_id',
        'product_id',
        'variant_id',
        'quantity',
        'quantity_received',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'quantity_received' => 'integer',
        ];
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(InventoryTransfer::class, 'transfer_id');
    }
}
