<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Database\Factories\InventoryBinFactory;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBin extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected $table = 'inventory_bins';

    protected static function newFactory(): InventoryBinFactory
    {
        return InventoryBinFactory::new();
    }

    protected $fillable = [
        'warehouse_id',
        'code',
        'zone',
        'aisle',
        'rack',
        'shelf',
        'is_pickable',
        'max_weight_kg',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_pickable' => 'boolean',
            'max_weight_kg' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function scopePickable($query)
    {
        return $query->where('is_pickable', true);
    }

    public function scopeByZone($query, string $zone)
    {
        return $query->where('zone', $zone);
    }
}
