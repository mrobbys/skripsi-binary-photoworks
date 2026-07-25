<?php

namespace App\Console\Commands;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Booking\Models\Booking;
use App\Jobs\SendWhatsappNotificationJob;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Support\Formatter;

#[Signature('booking:send-reminders')]
#[Description('Mengirim notifikasi WhatsApp pengingat H-1 jadwal pemotretan ke pelanggan')]
class SendBookingReminders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $tomorrow = Carbon::tomorrow()->toDateString();

        // Booking H-1 dengan status DP_PAID atau SUCCESS yang belum pernah dikirim reminder
        $targetBookings = Booking::with(['user', 'packageVariant.package', 'payments'])
            ->whereDate('booking_date', $tomorrow)
            ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS])
            ->whereNull('reminder_sent_at')
            ->cursor();

        $delayMinutes = (int) config('fonnte.delay_per_message', 1);
        $count = 0;
        $delay = 0;

        foreach ($targetBookings as $booking) {
            // Lewati jika user tidak memiliki nomor telepon terdaftar
            if (! $booking->user?->phone) {
                continue;
            }

            $message = $this->buildReminderMessage($booking);

            // Untuk pengiriman pesan WA menggunakan delay per pesan
            SendWhatsappNotificationJob::dispatch($booking->user->phone, $message)
                ->delay(now()->addMinutes($delay));

            // Tandai bahwa reminder sudah dikirim (mencegah pengiriman duplikat)
            $booking->update(['reminder_sent_at' => Carbon::now()]);

            $count++;
            $delay += $delayMinutes;
        }

        $this->info("Berhasil mengirim {$count} notifikasi WhatsApp reminder H-1.");
    }

    /**
     * Membangun template pesan reminder jadwal pemotretan.
     * Teks biasa tanpa icon/emoji.
     */
    private function buildReminderMessage(Booking $booking): string
    {
        $userName = $booking->user?->name ?? 'Pelanggan';
        $bookingCode = $booking->booking_code;
        $background = $booking->background?->name ?? 'Background';
        $packageName = $booking->packageVariant?->package?->name ?? 'Paket foto';
        $variantName = $booking->packageVariant?->name ?? '';

        $formattedDate = Formatter::dateId($booking->booking_date, 'l, d F Y');

        $timeRange = Formatter::timeRange($booking->start_time, $booking->end_time);

        $isDp = $booking->status === BookingStatus::DP_PAID;
        $totalPaid = (int) $booking->payments->where('status', PaymentStatus::SETTLEMENT)->sum('amount');
        $remaining = max(0, (int) $booking->total_price - $totalPaid);

        $paymentStatusText = $isDp ? BookingStatus::DP_PAID->label() : BookingStatus::SUCCESS->label();
        $remainingText = $isDp
            ? Formatter::rupiah($remaining)
            : 'Rp 0 (Sudah Lunas)';

        $pelunasanNote = $isDp
            ? 'Pelunasan sisa pembayaran dilakukan sebelum atau sesudah sesi di studio.'
            : 'Pembayaran Anda sudah lunas sepenuhnya.';

        return "Halo, *{$userName}*.\n\n"
            . "Ini adalah pengingat dari *Binary Photoworks* bahwa Anda memiliki jadwal sesi pemotretan besok.\n\n"
            . "*Detail Pemotretan:*\n"
            . "- Kode Booking : {$bookingCode}\n"
            . "- Paket : {$packageName} ({$variantName})\n"
            . "- Background: {$background}\n"
            . "- Hari/Tanggal : {$formattedDate}\n"
            . "- Jam : {$timeRange}\n"
            . "- Status Bayar : {$paymentStatusText}\n"
            . "- Sisa Tagihan : {$remainingText}\n\n"
            . "*Catatan:*\n"
            . "- Mohon hadir 10-15 menit sebelum sesi dimulai.\n"
            . "- {$pelunasanNote}\n\n"
            . "Jika ada pertanyaan, silakan balas pesan ini.\n"
            . "Terima kasih dan sampai jumpa besok.";
    }
}
