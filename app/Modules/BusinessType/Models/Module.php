<?php

declare(strict_types=1);

namespace App\Modules\BusinessType\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Module extends Model
{
    use CentralConnection;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'version',
        'provider_class',
        'dependencies',
        'required_features',
        'is_core',
    ];

    protected function casts(): array
    {
        return [
            'dependencies' => 'array',
            'required_features' => 'array',
            'is_core' => 'boolean',
        ];
    }
}
