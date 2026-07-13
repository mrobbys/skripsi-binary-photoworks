<?php

namespace App\Domains\Booking\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Domains\Booking\Enums\PaymentScheme;
use Illuminate\Validation\Rule;
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'package_variant_id' => ['required', 'integer', 'exists:package_variants,id'],
            'background_id' => ['nullable', 'integer', 'exists:backgrounds,id'],
            'booking_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'payment_scheme' => ['required', Rule::enum(PaymentScheme::class)],
            'notes' => ['nullable', 'string'],
            'addons' => ['nullable', 'array'],
            'addons.*.addon_id' => ['required', 'integer', 'exists:addons,id'],
            'addons.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
