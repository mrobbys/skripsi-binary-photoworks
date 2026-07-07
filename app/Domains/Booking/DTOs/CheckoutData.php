<?php

namespace App\Domains\Booking\DTOs;

use Spatie\LaravelData\Data;

class CheckoutData extends Data
{
    public function __construct(
        public readonly int $package_variant_id,
        public readonly ?int $background_id,
        public readonly string $booking_date,   // Y-m-d
        public readonly string $start_time,     // H:i
        public readonly string $payment_scheme, // 'lunas' | 'dp'
        /** @var array<int, array{addon_id: int, quantity: int}> */
        public readonly array $addons,
        public readonly ?string $keterangan,
    ) {}
}
