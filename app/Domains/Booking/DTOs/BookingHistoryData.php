<?php

namespace App\Domains\Booking\DTOs;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Support\Formatter;
use Carbon\Carbon;
use Spatie\LaravelData\Data;

class BookingHistoryData extends Data
{
  public function __construct(
    public readonly string $booking_code,
    public readonly string $package_name,
    public readonly string $variant_name,
    public readonly string $background_name,
    public readonly string $formatted_date,
    public readonly string $formatted_time,
    public readonly string $status,
    public readonly string $status_label,
    public readonly bool $can_pay,
    public readonly bool $can_cancel,
    public readonly bool $can_reschedule,
    public readonly int $variant_duration,
    public readonly ?string $payment_expiry_time,
    public readonly string $formatted_total_price,
    public readonly ?string $gdrive_link,
    public readonly ?string $receipt_url,
  ) {}

  public static function fromModel(Booking $booking): self
  {
    // Tombol "Bayar Sekarang" hanya muncul jika status masih PENDING
    $canPay = $booking->status === BookingStatus::PENDING;
    // Tombol "Batal" hanya muncul jika status masih PENDING
    $canCancel = $booking->status === BookingStatus::PENDING;

    /**
     * Reschedule hanya jika status PENDING, DP_PAID, atau SUCCESS
     * Dan waktu booking masih > 24 jam ke depan (H-1)
     * Batas maksimal reschedule adalah 3 kali
     */
    $maxReschedule = 3;
    $bookingDateTime = Carbon::parse($booking->booking_date)
      ->setTimeFrom(Carbon::parse($booking->start_time));
    $canReschedule   = in_array($booking->status, [BookingStatus::PENDING, BookingStatus::DP_PAID, BookingStatus::SUCCESS]) && $bookingDateTime->isAfter(Carbon::now()->addHours(24)) && $booking->reschedule_count < $maxReschedule;

    // Cari payment yang masih PENDING
    $pendingPayment = $booking->payments->where('status', PaymentStatus::PENDING)->first();
    $paymentExpiryTime = null;

    /**
     * Jika ada payment yang masih PENDING dan snap_token_expiry ada
     * Ambil waktu snap_token_expiry
     */
    if ($pendingPayment && $pendingPayment->snap_token_expiry) {
      $paymentExpiryTime = Carbon::parse($pendingPayment->snap_token_expiry)->format('H:i');
    }

    // Kuitansi pembayaran, hanya muncul jika status SETTLEMENT
    $payment = $booking->payments->where('status', PaymentStatus::SETTLEMENT)->first();
    $receiptUrl = $payment ? route('payments.receipt', ['booking' => $booking->booking_code]) : null;

    return new self(
      booking_code: $booking->booking_code,
      package_name: $booking->packageVariant?->package?->name ?? '-',
      variant_name: $booking->packageVariant?->name ?? '-',
      background_name: $booking->background?->name ?? '-',
      formatted_date: Formatter::dateId($booking->booking_date, 'l, d F Y'),
      formatted_time: Formatter::timeRange($booking->start_time, $booking->end_time),
      status: $booking->status->value,
      status_label: $booking->status->label(),
      can_pay: $canPay,
      can_cancel: $canCancel,
      can_reschedule: $canReschedule,
      variant_duration: $booking->packageVariant?->duration ?? 30,
      formatted_total_price: Formatter::rupiah($booking->total_price),
      payment_expiry_time: $paymentExpiryTime,
      gdrive_link: $booking->gdrive_link,
      receipt_url: $receiptUrl,
    );
  }
}
