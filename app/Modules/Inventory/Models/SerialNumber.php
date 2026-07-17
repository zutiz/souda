<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Database\Factories\SerialNumberFactory;
use App\Modules\Inventory\Enums\SerialStatusEnum;
use App\Modules\Product\Models\Product;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SerialNumber extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected $table = 'serial_numbers';

    protected static function newFactory(): SerialNumberFactory
    {
        return SerialNumberFactory::new();
    }

    protected $fillable = [
        'product_id',
        'variant_id',
        'serial_number',
        'status',
        'warehouse_id',
        'batch_id',
        'order_reference',
        'sold_at',
        'warranty_expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => SerialStatusEnum::class,
            'sold_at' => 'datetime',
            'warranty_expires_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }

    public function isUnderWarranty(): bool
    {
        return $this->warranty_expires_at !== null
            && Carbon::parse($this->warranty_expires_at)->isFuture();
    }

    public function markAsSold(string $orderReference): void
    {
        $this->update([
            'status' => SerialStatusEnum::Sold,
            'order_reference' => $orderReference,
            'sold_at' => now(),
        ]);
    }

    public function markAsReturned(): void
    {
        $this->update(['status' => SerialStatusEnum::Returned]);
    }

    public function markAsQuarantined(?string $notes = null): void
    {
        $this->update([
            'status' => SerialStatusEnum::Quarantined,
            'notes' => $notes,
        ]);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
