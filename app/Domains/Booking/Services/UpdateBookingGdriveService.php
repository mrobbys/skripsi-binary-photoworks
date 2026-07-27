<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Jobs\SendWhatsappNotificationJob;
use App\Support\Formatter;
use RuntimeException;

class UpdateBookingGdriveService
{
    public function execute(Booking $booking, string $gdriveLink, bool $isSendWa): void
    {
        if (! in_array($booking->status, [BookingStatus::SUCCESS, BookingStatus::DONE])) {
            throw new RuntimeException('Hanya booking yang sudah lunas yang dapat diupdate hasil fotonya.');
        }

        $booking->update([
            'gdrive_link' => $gdriveLink,
            'status' => BookingStatus::DONE
        ]);

        // Eager load relasi untuk menghindari N+1 query di WA message format
        $booking->loadMissing(['user', 'packageVariant.package']);

        if ($isSendWa && $booking->user?->phone) {
            SendWhatsappNotificationJob::dispatch(
                $booking->user->phone,
                $this->buildMessage($booking)
            );
        }
    }

    /**
     * Buat pesan untuk notifikasi whatsapp fonnte
     */
    private function buildMessage(Booking $booking): string
    {
        $code = $booking->booking_code;
        $user = $booking->user?->name;
        $package = $booking->packageVariant?->package?->name;
        $variant = $booking->packageVariant?->name;
        $gdriveLink = $booking->gdrive_link;
        $bookingDate = Formatter::dateId($booking->booking_date, 'l, d F Y');

        return <<<TEXT
Halo {$user}, sesi foto Anda telah selesai!

Berikut adalah rincian pesanan Anda:
*Kode Booking* : {$code}
*Paket* : {$package} - {$variant}
*Tanggal Sesi* : {$bookingDate}

Berikut adalah Link Google Drive untuk mengunduh hasil foto Anda:
{$gdriveLink}

Terima kasih telah mempercayakan momen berharga Anda kepada kami!
TEXT;
    }
}
