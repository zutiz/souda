<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Database\Factories\InventoryAlertFactory;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryAlert extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected $table = 'inventory_alerts';

    protected static function newFactory(): InventoryAlertFactory
    {
        return InventoryAlertFactory::new();
    }

    protected $fillable = [
        'rule_id',
        'type',
        'title',
        'message',
        'severity',
        'product_id',
        'warehouse_id',
        'dismissed_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'dismissed_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(InventoryRule::class, 'rule_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('dismissed_at')->whereNull('resolved_at');
    }

    public function scopeDismissed($query)
    {
        return $query->whereNotNull('dismissed_at');
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }

    public function dismiss(): void
    {
        $this->update(['dismissed_at' => now()]);
    }

    public function resolve(): void
    {
        $this->update(['resolved_at' => now()]);
    }
}
