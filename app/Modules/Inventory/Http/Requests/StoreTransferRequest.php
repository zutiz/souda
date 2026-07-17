<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'from_warehouse_id' => ['required', 'exists:inventory_warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:inventory_warehouses,id', 'different:from_warehouse_id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'exists:variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.batch_id' => ['nullable', 'exists:inventory_batches,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
