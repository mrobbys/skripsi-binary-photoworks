<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
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
    if ($booking->status !== BookingStatus::DP_PAID) {
      throw new RuntimeException('Booking ini tidak dalam status DP Terbayar.');
    }

    DB::transaction(function () use ($booking) {
      // Hitung total yang sudah masuk
      $totalPaid = $booking->payments()
        ->where('status', PaymentStatus::SETTLEMENT)
        ->sum('amount');

      $remaining = $booking->total_price - $totalPaid;

      if ($remaining <= 0) {
        throw new RuntimeException('Tagihan booking ini sudah lunas sepenuhnya.');
      }

      // Insert baris ledger pelunasan kasir
      Payment::create([
        'booking_id'      => $booking->id,
        'order_id'        => $booking->booking_code . '-PLN',
        'amount'          => $remaining,
        'status'          => PaymentStatus::SETTLEMENT,
        'payment_purpose' => PaymentPurpose::PELUNASAN,
        'payment_type'    => 'cash',
        'pay_date'        => now(),
      ]);

      // Update status booking
      $booking->update(['status' => BookingStatus::SUCCESS]);
    });
  }
}
