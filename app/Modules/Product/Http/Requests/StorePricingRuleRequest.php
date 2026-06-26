<?php

declare(strict_types=1);

namespace App\Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePricingRuleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:fixed,percentage,tiered'],
            'scope' => ['required', 'string', 'in:product,category,brand,all'],
            'scope_id' => ['required_if:scope,product', 'required_if:scope,category', 'required_if:scope,brand', 'integer', 'nullable'],
            'condition_type' => ['nullable', 'string', 'in:quantity,cart_total,customer_group,date_range'],
            'condition_value' => ['nullable', 'array'],
            'discount_value' => ['required', 'integer', 'min:0'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after:start_at'],
            'is_active' => ['boolean'],
            'priority' => ['integer', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
