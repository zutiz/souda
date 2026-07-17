<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Database\Factories\DemandForecastFactory;
use App\Modules\Product\Models\Product;
use App\Tenancy\Models\Concerns\HasTenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandForecast extends Model
{
    use HasFactory;
    use HasTenantScope;

    protected $table = 'demand_forecasts';

    protected static function newFactory(): DemandForecastFactory
    {
        return DemandForecastFactory::new();
    }

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'forecast_date',
        'forecast_quantity',
        'confidence_lower',
        'confidence_upper',
        'model_used',
        'period_start',
        'period_end',
        'actual_quantity',
        'accuracy_score',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'forecast_date' => 'date:Y-m-d',
            'forecast_quantity' => 'integer',
            'confidence_lower' => 'integer',
            'confidence_upper' => 'integer',
            'period_start' => 'date:Y-m-d',
            'period_end' => 'date:Y-m-d',
            'actual_quantity' => 'integer',
            'accuracy_score' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function scopePending($query)
    {
        return $query->whereNull('actual_quantity');
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('actual_quantity');
    }

    public function scopeUpcoming($query, int $days = 30)
    {
        return $query->whereBetween('forecast_date', [now(), now()->addDays($days)]);
    }

    public function scopeByProduct($query, string $productId, int $warehouseId)
    {
        return $query->where('product_id', $productId)->where('warehouse_id', $warehouseId);
    }

    public function recordActual(int $actualQuantity): void
    {
        $this->update([
            'actual_quantity' => $actualQuantity,
            'accuracy_score' => $this->computeAccuracy($actualQuantity),
        ]);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function computeAccuracy(int $actualQuantity): float
    {
        if ($this->forecast_quantity === 0) {
            return $actualQuantity === 0 ? 100.0 : 0.0;
        }

        $error = abs($actualQuantity - $this->forecast_quantity);
        $mape = ($error / max($this->forecast_quantity, 1)) * 100;

        return round(max(0, 100 - $mape), 2);
    }
}
