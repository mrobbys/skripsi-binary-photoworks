<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Repositories\BookingRepository;
use App\Domains\Payment\Enums\PaymentStatus;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CancelBookingService
{
  public function __construct(
    private readonly BookingRepository $repository
  ) {}

  /**
   * Cancel data booking
   * Jika $isAdmin = true, maka bisa dibatalkan oleh admin
   * @param string $bookingCode
   * @param int $userId
   * @param bool $isAdmin
   */
  public function execute(string $bookingCode, int $userId, bool $isAdmin = false): void
  {
    $booking = $this->repository->findByCodeAndUser($bookingCode, $userId);

    if (!$booking) {
      throw new RuntimeException('Booking tidak ditemukan.');
    }

    if (!$isAdmin && $booking->status !== BookingStatus::PENDING) {
      throw new RuntimeException('Booking ini tidak dapat dibatalkan.');
    }

    /**
     * Update status di table booking menjadi CANCELLED
     * Update semua status di table payment terkait menjadi CANCELLED
     * Hal ini karena jika admin membatalkan, uang direfund / dikembalikan dan transaksi dianggap batal
     */
    DB::transaction(function () use ($booking) {
      $booking->update(['status' => BookingStatus::CANCELLED]);
      $booking->payments()->update(['status' => PaymentStatus::CANCELLED]);
    });
  }
}
