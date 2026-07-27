<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Jobs\SendWhatsappNotificationJob;
use App\Support\Formatter;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SettleBookingService
{
  /**
   * Update data booking menjadi lunas
   * Insert baris payment ke-2 (sisa 40%) dan update status booking.
   * @param Booking $booking
   */
  public function execute(Booking $booking): void
  {
    DB::transaction(function () use ($booking) {
      // Re-fetch booking with lock to prevent race condition
      $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->first();

      if (! in_array($lockedBooking->status, [BookingStatus::DP_PAID, BookingStatus::SUCCESS])) {
        throw new RuntimeException('Booking ini tidak dapat dilunasi atau sudah diproses.');
      }

      // Hitung total yang sudah masuk menggunakan data terbaru
      $totalPaid = $lockedBooking->payments()
        ->where('status', PaymentStatus::SETTLEMENT)
        ->sum('amount');

      $remaining = $lockedBooking->total_price - $totalPaid;

      if ($remaining <= 0) {
        throw new RuntimeException('Tagihan booking ini sudah lunas sepenuhnya.');
      }

      $orderId = $lockedBooking->booking_code . '-PLN';
      if (Payment::where('order_id', $orderId)->exists()) {
        $orderId = $lockedBooking->booking_code . '-PLN-' . now()->timestamp;
      }

      Payment::create([
        'booking_id' => $lockedBooking->id,
        'order_id' => $orderId,
        'amount' => $remaining,
        'status' => PaymentStatus::SETTLEMENT,
        'payment_purpose' => PaymentPurpose::PELUNASAN,
        'payment_type' => 'manual',
        'pay_date' => now(),
      ]);

      // Update status booking di DB
      $lockedBooking->update(['status' => BookingStatus::SUCCESS]);

      // Sinkronisasikan status ke object di memori agar notifikasi WA mendapat data terbaru
      $booking->status = BookingStatus::SUCCESS;
    });

    // Kirim notifikasi WhatsApp
    SendWhatsappNotificationJob::dispatch(
      $booking->user->phone,
      $this->buildMessage($booking)
    );
  }

  /**
   * Membangun template pesan WhatsApp untuk Kuitansi Pelunasan
   */
  private function buildMessage(Booking $booking): string
  {
    $code = $booking->booking_code;
    $user = $booking->user?->name;
    $package = $booking->packageVariant?->package?->name;
    $variant = $booking->packageVariant?->name;

    $bookingDate = Formatter::dateId($booking->booking_date, 'l, d F Y');
    $sessionTime = Formatter::timeRange($booking->start_time, $booking->end_time);
    $totalPrice = Formatter::rupiah($booking->total_price);

    $dashboardUrl = route('frontdoor.dashboard.index');
    $receiptUrl = route('payments.receipt', ['booking' => $booking->booking_code]);

    return <<<TEXT
Halo {$user},

Terima kasih, pembayaran pelunasan untuk sesi foto Anda telah kami terima!

*Rincian Transaksi:*
*Kode Booking*: {$code}
*Paket*: {$package} ({$variant})
*Jadwal*: {$bookingDate} | {$sessionTime} WITA
*Total Biaya*: {$totalPrice}
*Status Pembayaran*: LUNAS

Unduh Kuitansi Pembayaran Anda (PDF):
{$receiptUrl}

Pantau status jadwal Anda di Dasbor Klien:
{$dashboardUrl}

Terima kasih telah mempercayakan momen berharga Anda kepada Binary Photoworks!
TEXT;
  }
}
