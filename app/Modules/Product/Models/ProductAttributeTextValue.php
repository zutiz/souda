<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeTextValue extends Model
{
    protected $table = 'product_attribute_text_values';

    public $timestamps = false;

    protected $fillable = [
        'product_attribute_value_id',
        'text_value',
    ];

    public function productAttributeValue(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeValue::class);
    }
}
