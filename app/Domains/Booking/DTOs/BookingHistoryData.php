<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Support\Formatter;
use Spatie\LaravelData\Data;

class BookingHistoryData extends Data
{
  public function __construct(
    public readonly int $id,
    public readonly string $booking_code,
    public readonly string $package_name,
    public readonly string $variant_name,
    public readonly string $background_name,
    public readonly string $formatted_date,
    public readonly string $formatted_time,
    public readonly string $status,
    public readonly string $status_label,
    // true = tab Akan Datang, false = tab Selesai
    public readonly bool $is_upcoming,
    // true = tampilkan tombol "Bayar Sekarang"
    public readonly bool $can_pay,
    public readonly string $formatted_total_price,
    public readonly string $payment_scheme,
    public readonly ?string $gdrive_link,
    public readonly ?string $receipt_url,
  ) {}

  public static function fromModel(Booking $booking): self
  {
    $upcomingStatuses = [
      BookingStatus::PENDING->value,
      BookingStatus::DP_PAID->value,
      BookingStatus::SUCCESS->value,
    ];

    $isUpcoming = in_array($booking->status->value, $upcomingStatuses);

    // Tombol "Bayar Sekarang" hanya muncul jika status masih "Menunggu"
    $canPay = $booking->status === BookingStatus::PENDING;

    $payment = $booking->payments->where('status', PaymentStatus::SETTLEMENT)->first();
    $receiptUrl = $payment ? route('payments.receipt', ['payment' => $payment->order_id]) : null;

    return new self(
      id: $booking->id,
      booking_code: $booking->booking_code,
      package_name: $booking->packageVariant?->package?->name ?? '-',
      variant_name: $booking->packageVariant?->name ?? '-',
      background_name: $booking->background?->name ?? '-',
      formatted_date: Formatter::dateId($booking->booking_date, 'l, d F Y'),
      formatted_time: Formatter::timeRange($booking->start_time, $booking->end_time),
      status: $booking->status->value,
      status_label: $booking->status->label(),
      is_upcoming: $isUpcoming,
      can_pay: $canPay,
      formatted_total_price: Formatter::rupiah($booking->total_price),
      payment_scheme: $booking->payment_scheme->value,
      gdrive_link: $booking->gdrive_link,
      receipt_url: $receiptUrl,
    );
  }
}
