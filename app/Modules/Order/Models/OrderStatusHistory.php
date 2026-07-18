<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    use HasTenantScope;
    use HasUlids;

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'changed_by',
        'reason',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
