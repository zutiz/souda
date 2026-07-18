<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateShipmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'courier' => ['required', 'string', 'in:pathao,steadfast,redx,sendo,paperfly'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['nullable', 'string', 'max:36'],
            'items.*.product_id' => ['nullable', 'string', 'max:36'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['integer', 'min:0'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'recipient_phone' => ['required', 'string', 'max:30'],
            'recipient_address' => ['required', 'string', 'max:500'],
            'recipient_city' => ['required', 'string', 'max:100'],
            'recipient_postal_code' => ['nullable', 'string', 'max:20'],
            'declared_value' => ['required', 'integer', 'min:0'],
            'cod_amount' => ['integer', 'min:0', 'default:0'],
            'total_weight_grams' => ['nullable', 'integer', 'min:0'],
            'service_type' => ['string', 'in:standard,express,same_day', 'default:standard'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
