<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Enums\PaymentScheme;
use App\Domains\Booking\Http\Requests\CheckoutRequest;
use Spatie\LaravelData\Data;

class CheckoutData extends Data
{
    public function __construct(
        public readonly int $package_variant_id,
        public readonly ?int $background_id,
        public readonly string $booking_date,   // Y-m-d
        public readonly string $start_time,     // H:i
        public readonly PaymentScheme $payment_scheme, // 'lunas' | 'dp'
        /** @var array<int, array{addon_id: int, quantity: int}> */
        public readonly array $addons,
        public readonly ?string $keterangan,
    ) {}

    public static function fromRequest(CheckoutRequest $request): self
    {
        return new self(
            package_variant_id: (int) $request->validated('package_variant_id'),
            background_id: $request->validated('background_id') ? (int) $request->validated('background_id') : null,
            booking_date: $request->validated('booking_date'),
            start_time: $request->validated('start_time'),
            payment_scheme: PaymentScheme::from($request->validated('payment_scheme')),
            keterangan: $request->validated('keterangan'),
            addons: $request->validated('addons') ?? [],
        );
    }
}
