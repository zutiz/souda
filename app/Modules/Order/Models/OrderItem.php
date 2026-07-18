<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasTenantScope;
    use HasUlids;

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'warehouse_id',
        'name',
        'sku',
        'barcode',
        'quantity',
        'unit_price',
        'total_price',
        'tax_amount',
        'discount_amount',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
