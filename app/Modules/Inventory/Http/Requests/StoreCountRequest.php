<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'type' => ['required', 'string', Rule::in(['full', 'cycle', 'partial'])],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['string', 'max:36'],
        ];
    }
}
