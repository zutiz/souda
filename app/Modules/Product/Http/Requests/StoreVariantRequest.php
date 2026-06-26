<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVariantRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'sku' => ['required', 'string', 'max:100', 'unique:variants,sku'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:variants,barcode'],
            'name' => ['required', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:0'],
            'compare_at_price' => ['nullable', 'integer', 'min:0'],
            'cost_price' => ['nullable', 'integer', 'min:0'],
            'track_inventory' => ['boolean'],
            'low_stock_threshold' => ['integer', 'min:0'],
            'attribute_value_ids' => ['required', 'array'],
            'attribute_value_ids.*' => ['integer', 'exists:attribute_values,id'],
            'is_default' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            'dimensions' => ['nullable', 'array'],
        ];
    }
}
