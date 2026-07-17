<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordCountItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:inventory_count_items,id'],
            'items.*.physical_quantity' => ['required', 'integer', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
