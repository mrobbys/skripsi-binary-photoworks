<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Enums\PaymentScheme;
use Spatie\LaravelData\Data;

class BookingData extends Data
{
    public function __construct(
        public readonly int $user_id,
        public readonly int $package_variant_id,
        public readonly int $background_id,
        public readonly string $booking_code,
        public readonly string $booking_date,
        public readonly string $start_time,
        public readonly string $end_time,
        public readonly int $total_price,
        public readonly PaymentScheme $payment_scheme,
        public readonly BookingStatus $status,
        public readonly ?string $notes = null,
    ) {}
}
