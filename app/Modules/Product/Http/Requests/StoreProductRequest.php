<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:500'],
            'slug' => ['nullable', 'string', 'max:500', 'alpha_dash', 'unique:products,slug'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:products,barcode'],
            'barcode_type' => ['nullable', 'string', 'in:ean13,upc,code128,qr'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'tax_category_id' => ['nullable', 'integer', 'exists:tax_categories,id'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'string', 'in:simple,configurable,bundle,virtual'],
            'status' => ['required', 'string', 'in:draft,active,archived'],
            'base_price' => ['required', 'integer', 'min:0'],
            'compare_at_price' => ['nullable', 'integer', 'min:0'],
            'cost_price' => ['nullable', 'integer', 'min:0'],
            'tax_inclusive' => ['boolean'],
            'track_inventory' => ['boolean'],
            'low_stock_threshold' => ['integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'dimensions' => ['nullable', 'array'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'attribute_values' => ['nullable', 'array'],
            'published_at' => ['nullable', 'date'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'This SKU is already in use.',
            'base_price.min' => 'Price must be a positive amount.',
        ];
    }
}
