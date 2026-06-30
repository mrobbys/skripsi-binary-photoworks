<?php

namespace App\Domains\Booking\Http\Requests;

use App\Domains\Booking\DTOs\CheckoutData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
            'payment_scheme' => ['required', 'in:lunas,dp'],
            'addons' => ['nullable', 'array'],
            'addons.*.addon_id' => ['required', 'integer', 'exists:addons,id'],
            'addons.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    public function toDto(): CheckoutData
    {
        return new CheckoutData(
            package_variant_id: (int) $this->validated('package_variant_id'),
            background_id: $this->validated('background_id') ? (int) $this->validated('background_id') : null,
            booking_date: $this->validated('booking_date'),
            start_time: $this->validated('start_time'),
            payment_scheme: $this->validated('payment_scheme'),
            addons: $this->validated('addons') ?? [],
        );
    }
}
