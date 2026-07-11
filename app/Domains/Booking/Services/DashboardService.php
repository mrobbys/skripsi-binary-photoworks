<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\DTOs\BookingHistoryData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Repositories\BookingRepository;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use RuntimeException;

class DashboardService
{
  public function __construct(
    private readonly BookingRepository $repository,
    private readonly MidtransService $midtrans,
  ) {}

  /**
   * Ambil semua booking milik user dan transform ke DTO
   * @param int $userId
   * @param string $tab
   * @param int $limit
   */
  public function getBookingHistory(int $userId, string $tab, int $limit = 5)
  {
    return $this->repository->getPaginatedByUser($userId, $tab, $limit)
      ->through(fn(Booking $b) => BookingHistoryData::fromModel($b));
  }

  /**
   * Fungsi utama: Reuse atau buat ulang Snap Token untuk booking yang sudah ada.
   * Hanya bisa dilakukan jika status booking masih PENDING.
   *
   * Alur:
   * 1. Cek apakah booking_code valid dan milik user yang login
   * 2. Pastikan status booking masih PENDING (boleh bayar)
   * 3. Cek payment record dengan status pending
   * 4. Jika snap_token ada dan belum expired -> kembalikan token lama
   * 5. Jika token expired atau tidak ada -> request token baru ke Midtrans, update record
   *
   * @param string $bookingCode
   * @param User   $user
   */
  public function getOrCreateSnapToken(string $bookingCode, User $user): string
  {
    $booking = $this->repository->findByCodeAndUser($bookingCode, $user->id);

    if (! $booking) {
      throw new RuntimeException('Booking tidak ditemukan.');
    }

    if ($booking->status !== BookingStatus::PENDING) {
      throw new RuntimeException('Booking ini tidak dapat dibayar (status bukan Menunggu).');
    }

    // Ambil payment record dengan status pending yang belum terbayar
    $payment = $booking->payments
      ->where('status', PaymentStatus::PENDING)
      ->first();

    if (! $payment) {
      throw new RuntimeException('Tidak ada tagihan yang perlu dibayar.');
    }

    // Jika snap_token masih ada dan belum expired -> reuse token
    if ($payment->snap_token && $payment->snap_token_expiry?->isFuture()) {
      return $payment->snap_token;
    }

    // Ambil order id bawaan
    $orderId = $payment->order_id;

    // Jika snap_token ada tapi expired -> Tolak dan ubah status jadi CANCELLED
    if ($payment->snap_token && $payment->snap_token_expiry?->isPast()) {
      // Ubah status jadi Batal
      $payment->update(['status' => PaymentStatus::CANCELLED]);
      $booking->update(['status' => BookingStatus::CANCELLED]);
      
      throw new RuntimeException('Batas waktu pembayaran (1 Jam) telah habis. Reservasi otomatis dibatalkan.');
    }

    // Token expired atau belum ada -> buat token baru ke Midtrans
    $snapToken = $this->midtrans->getSnapToken(
      orderId: $orderId,
      grossAmount: $payment->amount,
      user: $user,
      booking: $booking,
    );

    // Update snap_token dan expiry di database
    // Set expiry 1 jam dari sekarang
    $payment->update([
      'order_id' => $orderId,
      'snap_token' => $snapToken,
      'snap_token_expiry' => Carbon::now()->addHour(),
    ]);

    return $snapToken;
  }
}
