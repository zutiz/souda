<?php

namespace App\Http\Requests;

use App\Modules\Billing\Enums\SeatType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteTeamMemberRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'seat_type' => ['required', 'string', Rule::in([SeatType::Admin->value, SeatType::Staff->value])],
        ];
    }
}
