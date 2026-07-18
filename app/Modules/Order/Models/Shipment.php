<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use App\Modules\Order\Enums\ShipmentStatusEnum;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    use HasTenantScope;
    use HasUlids;

    protected $fillable = [
        'order_id',
        'shipment_number',
        'courier',
        'courier_service',
        'tracking_number',
        'tracking_url',
        'label_url',
        'status',
        'recipient_name',
        'recipient_phone',
        'recipient_address',
        'recipient_city',
        'recipient_postal_code',
        'shipping_cost',
        'cod_amount',
        'declared_value',
        'total_weight_grams',
        'total_items',
        'notes',
        'courier_response',
        'shipped_at',
        'estimated_delivery',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ShipmentStatusEnum::class,
            'courier_response' => 'array',
            'shipped_at' => 'datetime',
            'estimated_delivery' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function deliveryAttempts(): HasMany
    {
        return $this->hasMany(DeliveryAttempt::class);
    }
}
