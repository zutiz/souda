<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Models;

use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Model;

class TenantConfig extends Model
{
    use HasTenantScope;

    protected $connection = 'shared';

    protected $table = 'tenant_configs';

    protected $fillable = [
        'tenant_id',
        'business_type_slug',
        'config',
        'config_hash',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
        ];
    }
}
