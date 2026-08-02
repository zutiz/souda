<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReturn extends Model
{
    protected $table = 'order_returns';

    protected $fillable = [
        'order_id',
        'status',
        'reason',
        'items',
        'total_refund_amount',
        'processed_by',
        'processed_at',
        'notes',
    ];

    protected $casts = [
        'items' => 'array',
        'total_refund_amount' => 'integer',
        'processed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
