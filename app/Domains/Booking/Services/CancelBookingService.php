<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Repositories\BookingRepository;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Jobs\SendWhatsappNotificationJob;
use App\Support\Formatter;
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
      $booking->payments()->where('status', PaymentStatus::PENDING)->get()->each->update(['status' => PaymentStatus::CANCELLED]);
      $booking->payments()->where('status', PaymentStatus::SETTLEMENT)->get()->each->update(['status' => PaymentStatus::REFUNDED]);
    });

    // Relasi harus di-load sebelum membangun pesan
    $booking->loadMissing(['user', 'packageVariant.package']);

    // Kirim notifikasi WhatsApp
    SendWhatsappNotificationJob::dispatch(
      $booking->user->phone,
      $this->buildMessage($booking, $isAdmin)
    );
  }

  /**
   * Membangun template pesan WhatsApp untuk Pembatalan Booking
   */
  private function buildMessage(Booking $booking, bool $isAdmin): string
  {
    $code = $booking->booking_code;
    $user = $booking->user?->name;
    $package = $booking->packageVariant?->package?->name;
    $variant = $booking->packageVariant?->name;
    
    $bookingDate = Formatter::dateId($booking->booking_date, 'l, d F Y');
    $sessionTime = Formatter::timeRange($booking->start_time, $booking->end_time);

    $servicesUrl = route('frontdoor.services.index');

    if ($isAdmin) {
      return <<<TEXT
Halo {$user},

Informasi penting mengenai jadwal sesi foto Anda. Booking dengan rincian berikut telah *DIBATALKAN oleh Admin*:

*Detail Booking:*
*Kode Booking*: {$code}
*Paket*: {$package} - ({$variant})
*Jadwal Sebelumnya*: {$bookingDate} | {$sessionTime} WITA

Jika Anda merasa tidak melakukan permintaan pembatalan ini atau membutuhkan bantuan lebih lanjut terkait pengembalian dana (refund) / penjadwalan ulang (reschedule), silakan hubungi Customer Service kami segera dengan membalas pesan ini.

Terima kasih atas pengertian Anda.
TEXT;
    }

    return <<<TEXT
Halo {$user},

Booking Anda dengan rincian berikut telah berhasil *DIBATALKAN*:

*Detail Booking:*
*Kode Booking*: {$code}
*Paket*: {$package} - ({$variant})
*Jadwal*: {$bookingDate} | {$sessionTime} WITA

Jika ini adalah kesalahan atau Anda ingin membuat jadwal baru, silakan lakukan pemesanan kembali melalui tautan berikut:
{$servicesUrl}

Terima kasih.
TEXT;
  }
}
