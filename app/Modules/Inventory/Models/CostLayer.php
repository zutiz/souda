<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\CostingMethodEnum;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Warehouse;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostLayer extends Model
{
    use HasTenantScope;

    protected $table = 'cost_layers';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'unit_cost',
        'quantity_remaining',
        'quantity_original',
        'costing_method',
        'ledger_entry_id',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'integer',
            'quantity_remaining' => 'integer',
            'quantity_original' => 'integer',
            'costing_method' => CostingMethodEnum::class,
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

    public function ledgerEntry(): BelongsTo
    {
        return $this->belongsTo(InventoryLedger::class, 'ledger_entry_id');
    }
}
