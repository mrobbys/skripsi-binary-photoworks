<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Enums\PaymentScheme;
use App\Domains\Booking\Models\Booking;
use Spatie\LaravelData\Data;
use App\Support\Formatter;

class BookingViewData extends Data
{
  public function __construct(
    public readonly string $booking_code,
    public readonly string $package_name,
    public readonly string $variant_name,
    public readonly string $background_name,
    public readonly string $formatted_date,
    public readonly string $formatted_time,
    public readonly string $customer_name,
    public readonly string $customer_phone,
    public readonly string $formatted_total_price,
    public readonly PaymentScheme $payment_scheme,
    public readonly string $formatted_dp_amount,
    public readonly string $formatted_remaining_amount,
    public readonly string $order_id,
    public readonly ?string $notes,
    public readonly ?string $gdrive_link
  ) {}

  public static function fromModel(Booking $booking): self
  {
    $payment = $booking->payments?->first();
    $dpAmount = $payment?->amount ?? 0;

    return new self(
      booking_code: $booking->booking_code,
      package_name: $booking->packageVariant?->package?->name ?? '-',
      variant_name: $booking->packageVariant?->name ?? '-',
      background_name: $booking->background?->name ?? 'Tanpa Background',
      formatted_date: Formatter::dateId($booking->booking_date, 'l, d F Y'),
      formatted_time: Formatter::timeRange($booking->start_time, $booking->end_time),
      customer_name: $booking->user?->name ?? '-',
      customer_phone: $booking->user?->phone ?? '-',
      formatted_total_price: Formatter::rupiah($booking->total_price),
      payment_scheme: $booking->payment_scheme,
      formatted_dp_amount: Formatter::rupiah($dpAmount),
      formatted_remaining_amount: Formatter::rupiah($booking->total_price - $dpAmount),
      order_id: $payment?->order_id ?? '#',
      notes: $booking->notes,
      gdrive_link: $booking->gdrive_link
    );
  }
}
