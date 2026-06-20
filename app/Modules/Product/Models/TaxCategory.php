<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaxCategory extends Model
{
    use HasTenantScope;

    protected $table = 'tax_categories';

    protected $fillable = [
        'name',
        'description',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(TaxRate::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
