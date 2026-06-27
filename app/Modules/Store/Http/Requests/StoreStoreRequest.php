<?php

declare(strict_types=1);

namespace App\Modules\Store\Http\Requests;

use App\Modules\Store\Enums\StoreStatusEnum;
use App\Modules\Store\Models\Store;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique(Store::class, 'slug')],
            'code' => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique(Store::class, 'code')],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'currency' => ['nullable', 'string', 'max:3'],
            'locale' => ['nullable', 'string', 'max:10'],
            'status' => ['nullable', 'string', Rule::in(array_column(StoreStatusEnum::cases(), 'value'))],
            'is_default' => ['boolean'],
            'business_hours' => ['nullable', 'array'],
            'config' => ['nullable', 'array'],
            'pos_settings' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Store name is required.',
            'slug.alpha_dash' => 'Store slug may only contain letters, numbers, dashes, and underscores.',
            'code.alpha_dash' => 'Store code may only contain letters, numbers, dashes, and underscores.',
        ];
    }
}
