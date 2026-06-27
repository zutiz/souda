<?php

declare(strict_types=1);

namespace App\Modules\Store\Models;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Models\Warehouse;
use App\Modules\Store\Database\Factories\StoreFactory;
use App\Modules\Store\Enums\StoreStatusEnum;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Store extends Model
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory, HasTenantScope, SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'email',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'timezone',
        'currency',
        'locale',
        'status',
        'is_default',
        'business_hours',
        'config',
        'pos_settings',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
            'business_hours' => 'array',
            'config' => 'array',
            'pos_settings' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Store $store) {
            if (! $store->id) {
                $store->id = (string) Str::ulid();
            }

            if (! $store->slug) {
                $store->slug = Str::slug($store->name);
            }

            if (! $store->code) {
                $store->code = strtoupper(Str::random(6));
            }

            if (! $store->status) {
                $store->status = StoreStatusEnum::Active;
            }

            if (! $store->timezone) {
                $store->timezone = config('app.timezone', 'UTC');
            }

            if (! $store->currency) {
                $store->currency = config('billing.currency', 'BDT');
            }
        });
    }

    protected static function newFactory(): StoreFactory
    {
        return StoreFactory::new();
    }

    public function isActive(): bool
    {
        return $this->status === StoreStatusEnum::Active->value;
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'store_product')
            ->withPivot([
                'price', 'compare_at_price',
                'is_visible', 'is_featured',
                'status', 'sort_order',
            ])
            ->withTimestamps();
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'store_warehouse')
            ->withPivot(['is_default_for_receiving', 'is_default_for_fulfillment'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', StoreStatusEnum::Active->value);
    }

    public function scopeDefault(Builder $query): void
    {
        $query->where('is_default', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }
}
