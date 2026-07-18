<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Models\Booking;
use App\Support\Formatter;
use Spatie\LaravelData\Data;

class ClientBookingHistoryData extends Data
{
  public function __construct(
    public readonly int $id,
    public readonly string $booking_code,
    public readonly string $formatted_date,
    public readonly string $package_name,
    public readonly string $variant_name,
    public readonly string $status,
    public readonly string $formatted_price,
  ) {}

  public static function fromModel(Booking $booking): self
  {
    $formattedDate = Formatter::dateId($booking->booking_date, 'l, d F Y')
      . ' · ' .
      Formatter::timeRange($booking->start_time, $booking->end_time);

    return new self(
      id: $booking->id,
      booking_code: $booking->booking_code,
      formatted_date: $formattedDate,
      package_name: $booking->packageVariant->package->name,
      variant_name: $booking->packageVariant->name,
      status: $booking->status->value,
      formatted_price: Formatter::rupiah($booking->total_price),
    );
  }
}
