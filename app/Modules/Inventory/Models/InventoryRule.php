<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Database\Factories\InventoryRuleFactory;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryRule extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected $table = 'inventory_rules';

    protected static function newFactory(): InventoryRuleFactory
    {
        return InventoryRuleFactory::new();
    }

    protected $fillable = [
        'name',
        'description',
        'condition_type',
        'condition_config',
        'action_type',
        'action_config',
        'is_active',
        'schedule',
        'last_run_at',
    ];

    protected function casts(): array
    {
        return [
            'condition_config' => 'array',
            'action_config' => 'array',
            'is_active' => 'boolean',
            'last_run_at' => 'datetime',
        ];
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(InventoryAlert::class, 'rule_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
