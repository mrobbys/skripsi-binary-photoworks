<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Attributes\Description;
use App\Domains\Payment\Models\Payment;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Booking\Enums\BookingStatus;
use Carbon\Carbon;

#[Signature('booking:cancel-expired')]
#[Description('Membatalkan otomatis booking yang belum dibayar hingga batas waktu habis')]
class CancelExpiredBookings extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Cari semua payment yang masih PENDING tapi sudah lewat batas waktu
        $expiredPayments = Payment::with('booking')
            ->where('status', PaymentStatus::PENDING)
            ->whereNotNull('snap_token_expiry')
            ->where('snap_token_expiry', '<', Carbon::now())
            ->cursor();

        $count = 0;

        foreach ($expiredPayments as $payment) {
            // Ubah status Payment jadi CANCELLED
            $payment->update(['status' => PaymentStatus::CANCELLED]);

            // Ubah status Booking jadi CANCELLED
            if ($payment->booking && $payment->booking->status !== BookingStatus::CANCELLED) {
                $payment->booking->update(['status' => BookingStatus::CANCELLED]);
            }
            $count++;
        }

        $this->info("Berhasil membatalkan {$count} booking yang kadaluarsa.");
    }
}
