<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\MovementTypeEnum;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Variant;
use App\Modules\Product\Models\Warehouse;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLedger extends Model
{
    use HasTenantScope;

    protected $table = 'inventory_ledger';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'bin_id',
        'quantity',
        'quantity_before',
        'quantity_after',
        'movement_type',
        'reference',
        'reference_type',
        'batch_id',
        'serial_numbers',
        'unit_cost',
        'total_cost',
        'description',
        'metadata',
        'created_by',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'movement_type' => MovementTypeEnum::class,
            'serial_numbers' => 'array',
            'metadata' => 'array',
            'quantity' => 'integer',
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
            'unit_cost' => 'integer',
            'total_cost' => 'integer',
            'created_at' => 'datetime',
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
