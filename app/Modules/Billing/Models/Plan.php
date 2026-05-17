<?php

namespace App\Modules\Billing\Models;

use Database\Factories\PlanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class Plan extends Model
{
    use CentralConnection, HasFactory;

    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }

    protected $table = 'billing_plans';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'integer',
            'yearly_price' => 'integer',
            'features' => 'array',
            'limits' => 'array',
            'is_active' => 'boolean',
            'popular' => 'boolean',
            'trial_enabled' => 'boolean',
            'trial_days' => 'integer',
            'trial_without_card' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('display_order');
    }

    public function scopeBySlug(Builder $query, string $slug): void
    {
        $query->where('slug', $slug);
    }
}
