<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttributeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', 'unique:attributes,slug'],
            'frontend_type' => ['required', 'string', 'in:select,multi_select,text,textarea,color,swatch'],
            'is_filterable' => ['boolean'],
            'is_required' => ['boolean'],
            'is_variant' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            'validation_rules' => ['nullable', 'array'],
        ];
    }
}
