<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\DTOs\BookingHistoryData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Services\SlotAvailabilityService;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DashboardService
{
  public function __construct(
    private readonly SlotAvailabilityService $slotAvailabilityService,
  ) {}

  /**
   * Ambil semua booking milik user dan transform ke DTO
   * @param int $userId
   * @param string $tab
   * @param int $limit
   */
  public function getBookingHistory(int $userId, string $tab, int $limit = 5)
  {
    $query = Booking::select([
      'id',
      'user_id',
      'package_variant_id',
      'background_id',
      'booking_code',
      'booking_date',
      'start_time',
      'end_time',
      'status',
      'total_price',
      'gdrive_link',
      'reschedule_count',
      'created_at',
    ])
      ->with([
        'packageVariant:id,package_id,name,duration',
        'packageVariant.package:id,name',
        'background:id,name',
        'payments:id,booking_id,status,snap_token_expiry',
      ])
      ->where('user_id', $userId);

    if ($tab === 'upcoming') {
      $query->whereIn('status', [
        BookingStatus::PENDING,
        BookingStatus::DP_PAID,
        BookingStatus::SUCCESS
      ]);
    } else {
      $query->whereIn('status', [
        BookingStatus::DONE,
        BookingStatus::CANCELLED
      ]);
    }

    $paginated = $query->orderBy('created_at', 'desc')
      ->orderBy('start_time', 'desc')
      ->paginate($limit);

    return $paginated->through(fn(Booking $b) => BookingHistoryData::fromModel($b));
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
    $booking = Booking::select(['id', 'booking_code', 'user_id', 'status'])
      ->with('payments:id,booking_id,status,snap_token,snap_token_expiry')
      ->where('booking_code', $bookingCode)
      ->where('user_id', $user->id)
      ->first();

    if (! $booking) {
      throw new RuntimeException('Booking tidak ditemukan.');
    }

    // Pastikan status booking masih PENDING (boleh bayar)
    if ($booking->status !== BookingStatus::PENDING) {
      throw new RuntimeException('Booking ini tidak dapat dibayar (status bukan Menunggu).');
    }

    // Ambil data payment dengan status pending
    $payment = $booking->payments->where('status', PaymentStatus::PENDING)->first();

    // Jika data payment atau snap_token tidak ada -> tolak
    if (! $payment || ! $payment->snap_token) {
      throw new RuntimeException('Tagihan atau token pembayaran tidak ditemukan.');
    }

    // Jika token expired -> batalkan payment dan booking
    if ($payment->snap_token_expiry?->isPast()) {
      DB::transaction(function () use ($payment, $booking) {
        $payment->update(['status' => PaymentStatus::CANCELLED]);
        $booking->update(['status' => BookingStatus::CANCELLED]);
      });

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
    $booking = Booking::select([
      'id',
      'booking_code',
      'user_id',
      'package_variant_id',
      'status',
      'reschedule_count',
      'booking_date',
      'start_time',
    ])
      ->with('packageVariant:id,duration')
      ->where('booking_code', $bookingCode)
      ->where('user_id', $userId)
      ->first();

    if (! $booking) {
      throw new RuntimeException('Booking tidak ditemukan.');
    }

    $allowedStatuses = [BookingStatus::PENDING, BookingStatus::DP_PAID, BookingStatus::SUCCESS];
    if (! in_array($booking->status, $allowedStatuses)) {
      throw new RuntimeException('Booking ini tidak dapat diubah jadwalnya.');
    }

    // Cek apakah sudah mencapai batas maksimal reschedule (3 kali)
    if ($booking->reschedule_count >= $maxRescheduleCount) {
      throw new RuntimeException('Jadwal sudah melebihi batas reschedule (3 kali).');
    }

    // Validasi H-1: harus > 24 jam sebelum jadwal awal
    $originalDateTime = $booking->booking_date->copy()->setTimeFrom($booking->start_time);
    if (! $originalDateTime->isAfter(Carbon::now()->addHours(24))) {
      throw new RuntimeException('Jadwal sudah terlalu dekat untuk diubah (batas H-1).');
    }

    // Hitung end_time baru berdasarkan durasi variant, default 30 menit
    $duration = $booking->packageVariant?->duration ?? 30;
    $newEndTime = Carbon::parse($newStartTime)->addMinutes($duration)->format('H:i');

    // Validasi slot baru tidak bentrok dengan booking lain (kecuali booking sendiri)
    if ($this->slotAvailabilityService->isSlotOccupiedExcluding($newDate, $newStartTime, $newEndTime, $booking->id)) {
      throw new RuntimeException('Slot waktu yang dipilih sudah terisi. Silakan pilih waktu lain.');
    }

    // Update jadwal dan increment reschedule_count dalam transaction
    DB::transaction(function () use ($booking, $newDate, $newStartTime, $newEndTime) {
      $booking->update([
        'booking_date' => $newDate,
        'start_time' => $newStartTime,
        'end_time' => $newEndTime,
      ]);

      $booking->increment('reschedule_count');
    });
  }
}
