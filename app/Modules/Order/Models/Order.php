<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use App\Modules\Order\Database\Factories\OrderFactory;
use App\Modules\Order\Enums\FulfillmentStatusEnum;
use App\Modules\Order\Enums\OrderStatusEnum;
use App\Modules\Order\Enums\OrderTypeEnum;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use HasTenantScope;
    use HasUlids;
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'tenant_id',
        'order_number',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'status',
        'order_type',
        'fulfillment_status',
        'payment_status',
        'currency',
        'subtotal',
        'shipping_total',
        'tax_total',
        'discount_total',
        'grand_total',
        'paid_total',
        'refund_total',
        'due_total',
        'coupon_code',
        'payment_method',
        'payment_reference',
        'notes',
        'shipping_name',
        'shipping_phone',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
        'billing_name',
        'billing_phone',
        'billing_address_line_1',
        'billing_address_line_2',
        'billing_city',
        'billing_state',
        'billing_postal_code',
        'billing_country',
        'source',
        'created_by',
        'metadata',
        'placed_at',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatusEnum::class,
            'order_type' => OrderTypeEnum::class,
            'fulfillment_status' => FulfillmentStatusEnum::class,
            'metadata' => 'array',
            'placed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
