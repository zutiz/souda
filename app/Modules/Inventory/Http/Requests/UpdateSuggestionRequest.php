<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSuggestionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:ordered,dismissed'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'order_reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
