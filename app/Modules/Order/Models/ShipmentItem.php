<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentItem extends Model
{
    use HasTenantScope;
    use HasUlids;

    protected $fillable = [
        'shipment_id',
        'order_item_id',
        'product_id',
        'variant_id',
        'name',
        'sku',
        'quantity',
        'unit_price',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
