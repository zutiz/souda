<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Models;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class BusinessType extends Model
{
    use CentralConnection;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'is_active',
        'pack_class',
        'config_template',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'config_template' => 'array',
            'metadata' => 'array',
        ];
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'business_type_module')
            ->withPivot(['is_required', 'config_defaults'])
            ->withTimestamps();
    }

    public function requiredModules(): BelongsToMany
    {
        return $this->modules()->wherePivot('is_required', true);
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'business_type_id');
    }
}
