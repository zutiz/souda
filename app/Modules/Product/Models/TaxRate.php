<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    protected $table = 'tax_rates';

    protected $fillable = [
        'tax_category_id',
        'name',
        'rate',
        'country',
        'state',
        'postal_code',
        'is_compound',
        'is_active',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:2',
            'is_compound' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    public function taxCategory(): BelongsTo
    {
        return $this->belongsTo(TaxCategory::class);
    }
}
