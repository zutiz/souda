<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Database\Factories\WarehouseFactory;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Warehouse extends Model
{
    use HasFactory;
    use HasTenantScope;
    use SoftDeletes;

    protected $table = 'inventory_warehouses';

    protected static function newFactory(): WarehouseFactory
    {
        return WarehouseFactory::new();
    }

    protected $fillable = [
        'name',
        'slug',
        'type',
        'code',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'is_active',
        'is_default',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'metadata' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Warehouse $warehouse) {
            if (empty($warehouse->slug)) {
                $warehouse->slug = Str::slug($warehouse->name);
            }
        });
    }

    public function bins(): HasMany
    {
        return $this->hasMany(InventoryBin::class, 'warehouse_id');
    }

    public function inboundTransfers(): HasMany
    {
        return $this->hasMany(InventoryTransfer::class, 'to_warehouse_id');
    }

    public function outboundTransfers(): HasMany
    {
        return $this->hasMany(InventoryTransfer::class, 'from_warehouse_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
