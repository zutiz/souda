<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'string', 'max:36'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'order_type' => ['required', 'string', 'in:dine_in,takeaway,delivery,in_store,online,wholesale,pre_order'],
            'currency' => ['string', 'size:3', 'default:BDT'],
            'subtotal' => ['required', 'integer', 'min:0'],
            'tax_total' => ['integer', 'min:0', 'default:0'],
            'shipping_total' => ['integer', 'min:0', 'default:0'],
            'discount_total' => ['integer', 'min:0', 'default:0'],
            'grand_total' => ['required', 'integer', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'payment_status' => ['string', 'in:pending,paid,partially_paid,refunded,failed', 'default:pending'],
            'notes' => ['nullable', 'string'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'source' => ['string', 'in:pos,online,admin,api', 'default:pos'],
            'line_items' => ['required', 'array', 'min:1'],
            'line_items.*.product_id' => ['required', 'string', 'max:36'],
            'line_items.*.variant_id' => ['nullable', 'string', 'max:36'],
            'line_items.*.name' => ['required', 'string', 'max:255'],
            'line_items.*.sku' => ['nullable', 'string', 'max:100'],
            'line_items.*.quantity' => ['required', 'integer', 'min:1'],
            'line_items.*.unit_price' => ['required', 'integer', 'min:0'],
            'line_items.*.total_price' => ['required', 'integer', 'min:0'],
            'line_items.*.tax_amount' => ['integer', 'min:0'],
            'line_items.*.discount_amount' => ['integer', 'min:0'],
            'line_items.*.warehouse_id' => ['nullable', 'string', 'max:36'],
            'shipping_name' => ['nullable', 'string', 'max:255'],
            'shipping_phone' => ['nullable', 'string', 'max:30'],
            'shipping_address_line_1' => ['nullable', 'string', 'max:255'],
            'shipping_address_line_2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['nullable', 'string', 'max:100'],
            'shipping_state' => ['nullable', 'string', 'max:100'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_country' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'line_items.required' => 'At least one item is required.',
            'line_items.min' => 'At least one item is required.',
            'subtotal.min' => 'Subtotal must be a positive amount.',
            'grand_total.min' => 'Grand total must be a positive amount.',
        ];
    }
}
