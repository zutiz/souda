<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\CountItemStatusEnum;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Variant;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Database\Factories\InventoryCountItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryCountItem extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected $table = 'inventory_count_items';

    protected static function newFactory(): InventoryCountItemFactory
    {
        return InventoryCountItemFactory::new();
    }

    protected $fillable = [
        'count_id',
        'product_id',
        'variant_id',
        'bin_id',
        'expected_quantity',
        'physical_quantity',
        'discrepancy',
        'unit_cost',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expected_quantity' => 'integer',
            'physical_quantity' => 'integer',
            'discrepancy' => 'integer',
            'unit_cost' => 'integer',
            'status' => CountItemStatusEnum::class,
        ];
    }

    public function count(): BelongsTo
    {
        return $this->belongsTo(InventoryCount::class, 'count_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'variant_id');
    }

    public function bin(): BelongsTo
    {
        return $this->belongsTo(InventoryBin::class, 'bin_id');
    }
}
