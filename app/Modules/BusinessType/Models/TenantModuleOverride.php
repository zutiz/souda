<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Models;

use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

class TenantModuleOverride extends Model
{
    use HasTenantScope;

    protected $connection = 'shared';

    protected $table = 'tenant_module_overrides';

    protected $fillable = [
        'tenant_id',
        'module_slug',
        'is_enabled',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'settings' => 'array',
        ];
    }
}
