<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PricingRule extends Model
{
    protected $table = 'pricing_rules';

    protected $fillable = [
        'name',
        'type',
        'scope',
        'scope_id',
        'condition_type',
        'condition_value',
        'discount_value',
        'start_at',
        'end_at',
        'is_active',
        'priority',
        'max_uses',
        'used_count',
    ];

    protected function casts(): array
    {
        return [
            'condition_value' => 'array',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_active' => 'boolean',
            'discount_value' => 'integer',
            'priority' => 'integer',
            'max_uses' => 'integer',
            'used_count' => 'integer',
        ];
    }

    public function scopeModel(): MorphTo
    {
        return $this->morphTo();
    }

    public function isExpired(): bool
    {
        if ($this->end_at !== null && $this->end_at->isPast()) {
            return true;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return true;
        }

        return false;
    }
}
