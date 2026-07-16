<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Enums\PaymentScheme;
use App\Domains\Booking\Models\Booking;
use App\Support\Formatter;
use Spatie\LaravelData\Data;

class ManageBookingIndexData extends Data
{
  public function __construct(
    public readonly int $id,
    public readonly string $booking_code,
    public readonly string $user_name,
    public readonly string $formatted_date,
    public readonly string $formatted_time,
    public readonly string $package_name,
    public readonly string $variant_name,
    public readonly string $formatted_total_price,
    public readonly string $payment_detail_label,
    public readonly string $status,
  ) {}
  
  public static function fromModel(Booking $booking): self
  {
    $formattedTotal = Formatter::rupiah($booking->total_price);

    // sub label price (dp atau lunas)
    if ($booking->payment_scheme === PaymentScheme::DP) {
      // dp 60%
      $dpAmount = $booking->total_price * 0.6;
      $paymentDetailLabel = "(DP: " . Formatter::rupiah($dpAmount) . ")";
    } else {
      $paymentDetailLabel = "(Lunas)";
    }

    return new self(
      id: $booking->id,
      booking_code: $booking->booking_code,
      user_name: $booking->user?->name ?? '-',
      formatted_date: Formatter::dateId($booking->booking_date, 'l, d F Y'),
      formatted_time: Formatter::timeRange($booking->start_time, $booking->end_time),
      package_name: $booking->packageVariant?->package?->name ?? '-',
      variant_name: $booking->packageVariant?->name ?? '-',
      formatted_total_price: $formattedTotal,
      payment_detail_label: $paymentDetailLabel ?? "",
      status: $booking->status->value,
    );
  }
}
