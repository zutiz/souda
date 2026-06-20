<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Modules\Product\Enums\MovementTypeEnum;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasTenantScope;
    use HasUlids;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'variant_id',
        'movement_type',
        'quantity',
        'reference_type',
        'reference_id',
        'notes',
        'performed_by',
        'snapshot_before',
        'snapshot_after',
        'audit_log_id',
    ];

    protected function casts(): array
    {
        return [
            'movement_type' => MovementTypeEnum::class,
            'snapshot_before' => 'array',
            'snapshot_after' => 'array',
            'quantity' => 'integer',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function auditLog(): BelongsTo
    {
        return $this->belongsTo(AuditLog::class);
    }
}
