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
   * @param string $bookingCode
   * @param User   $user
   */
  public function getValidSnapToken(string $bookingCode, User $user): string
  {
    $booking = $this->repository->findByCodeAndUser($bookingCode, $user->id);

    if (! $booking) {
      throw new RuntimeException('Booking tidak ditemukan.');
    }

    // Pastikan status booking masih PENDING
    if ($booking->status !== BookingStatus::PENDING) {
      throw new RuntimeException('Booking ini tidak dapat dibayar (status bukan Menunggu).');
    }

    // Ambil data payment dengan status pending
    $payment = $booking->payments->where('status', PaymentStatus::PENDING)->first();

    // Jika data payment atau snap_token tidak ada -> tolak
    if (! $payment || ! $payment->snap_token) {
      throw new RuntimeException('Tagihan atau token pembayaran tidak ditemukan.');
    }

    // Jika token expired -> Tolak dan ubah status jadi CANCELLED
    if ($payment->snap_token_expiry?->isPast()) {
      $payment->update(['status' => PaymentStatus::CANCELLED]);
      $booking->update(['status' => BookingStatus::CANCELLED]);

      throw new RuntimeException('Batas waktu pembayaran (1 Jam) telah habis. Reservasi otomatis dibatalkan.');
    }

    return $payment->snap_token;
  }

  /**
   * Ubah jadwal booking milik user.
   * Aturan:
   * - Status harus PENDING atau DP_PAID
   * - Booking harus masih > 24 jam ke depan (H-1)
   * - Slot baru tidak boleh bentrok dengan booking lain
   * @param string $bookingCode
   * @param int    $userId
   * @param string $newDate       Format: Y-m-d
   * @param string $newStartTime  Format: H:i
   */
  public function rescheduleBooking(string $bookingCode, int $userId, string $newDate, string $newStartTime): void
  {
    $maxRescheduleCount = 3;
    $booking = $this->repository->findByCodeAndUser($bookingCode, $userId);

    if (! $booking) {
      throw new RuntimeException('Booking tidak ditemukan.');
    }

    $allowedStatuses = [BookingStatus::PENDING, BookingStatus::DP_PAID, BookingStatus::SUCCESS];
    if (! in_array($booking->status, $allowedStatuses)) {
      throw new RuntimeException('Booking ini tidak dapat diubah jadwalnya.');
    }

    // Cek apakah reschedule_count = 3, jika valid throw error
    if ($booking->reschedule_count >= $maxRescheduleCount) {
      throw new RuntimeException('Jadwal sudah melebihi batas reschedule (3 kali).');
    }

    // Validasi H-1: harus > 24 jam sebelum jadwal awal
    $originalDateTime = $booking->booking_date->copy()->setTimeFrom($booking->start_time);
    if (! $originalDateTime->isAfter(Carbon::now()->addHours(24))) {
      throw new RuntimeException('Jadwal sudah terlalu dekat untuk diubah (batas H-1).');
    }

    // Hitung end_time baru berdasarkan durasi variant
    // Ambil durasi dari variant jika tidak ada maka default 30 menit
    $duration   = $booking->packageVariant?->duration ?? 30;
    $newEndTime = Carbon::parse($newStartTime)->addMinutes($duration)->format('H:i');

    // Validasi slot baru tidak bentrok (kecuali dengan booking sendiri)
    if ($this->repository->isSlotOccupiedExcluding($newDate, $newStartTime, $newEndTime, $booking->id)) {
      throw new RuntimeException('Slot waktu yang dipilih sudah terisi. Silakan pilih waktu lain.');
    }

    // Update jadwal
    $booking->update([
      'booking_date' => $newDate,
      'start_time'   => $newStartTime,
      'end_time'     => $newEndTime,
    ]);

    // Tambah reschedule_count, untuk membatasi reschedule maksimal
    $booking->increment('reschedule_count');
  }
}
