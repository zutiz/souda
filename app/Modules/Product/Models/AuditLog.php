<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AuditLog extends Model
{
    use HasTenantScope;
    use HasUlids;

    const UPDATED_AT = null;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'user_name',
        'entity_type',
        'entity_id',
        'action',
        'old_values',
        'new_values',
        'changed_fields',
        'reference_type',
        'reference_id',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'changed_fields' => 'array',
        ];
    }

    public function stockMovement(): HasOne
    {
        return $this->hasOne(StockMovement::class, 'audit_log_id');
    }
}
