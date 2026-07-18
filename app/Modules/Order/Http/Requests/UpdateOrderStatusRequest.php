<?php

declare(strict_types=1);

namespace App\Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:pending,confirmed,processing,ready_for_pickup,out_for_delivery,partially_shipped,shipped,delivered,completed,cancelled,refunded,partially_refunded,on_hold,failed'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Invalid order status.',
        ];
    }
}
