<?php

namespace App\Console\Commands;

use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Enums\PaymentScheme;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Services\MidtransService;
use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Jobs\SendWhatsappNotificationJob;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[Signature('waitinglist:promote')]
#[AsCommand(name: 'waitinglist:promote')]
#[Description('Promote waiting list bookings if slot becomes available')]
class PromoteWaitingListCommand extends Command
{
    public function handle(MidtransService $midtrans): int
    {
        $today = Carbon::today()->toDateString();

        // Ambil slot (booking_date & start_time) yang masih memiliki WAITING_LIST mulai hari ini ke depan.
        $waitingSlots = Booking::select('booking_date', 'start_time')
            ->where('status', BookingStatus::WAITING_LIST)
            ->where('booking_date', '>=', $today)
            ->distinct()
            ->get();

        $promotedCount = 0;

        foreach ($waitingSlots as $slot) {
            // Cek apakah ada yang menempati slot ini (PENDING / DP_PAID / SUCCESS)
            $isOccupied = Booking::where('booking_date', $slot->booking_date)
                ->where('start_time', $slot->start_time)
                ->whereIn('status', [BookingStatus::PENDING, BookingStatus::DP_PAID, BookingStatus::SUCCESS])
                ->exists();

            if (!$isOccupied) {
                // Tarik antrean paling depan
                $promotedBooking = Booking::with(['user', 'packageVariant.package.category'])
                    ->where('booking_date', $slot->booking_date)
                    ->where('start_time', $slot->start_time)
                    ->where('status', BookingStatus::WAITING_LIST)
                    ->orderBy('id', 'asc')
                    ->first();

                if ($promotedBooking) {
                    $this->promoteBooking($promotedBooking, $midtrans);
                    $promotedCount++;
                }
            }
        }

        if ($promotedCount > 0) {
            $this->info("Berhasil mempromosikan {$promotedCount} data waiting list.");
        }

        return self::SUCCESS;
    }

    private function promoteBooking(Booking $booking, MidtransService $midtrans): void
    {
        DB::transaction(function () use ($booking, $midtrans) {
            $variant = $booking->packageVariant;
            $user = $booking->user;

            $grossAmount = $booking->payment_scheme === PaymentScheme::DP
                ? (int) round($booking->total_price * PaymentScheme::DP_RATE)
                : $booking->total_price;

            $suffix = $booking->payment_scheme === PaymentScheme::DP ? 'DP' : 'FULL';
            $randomString = Str::upper(Str::random(3));
            $orderId = "{$booking->booking_code}-{$suffix}-{$randomString}";

            $snapToken = $midtrans->getSnapToken(
                orderId: $orderId,
                grossAmount: $grossAmount,
                user: $user,
                bookingId: $booking->id,
                bookingCode: $booking->booking_code,
                packageName: $variant->package->name,
                variantName: $variant->name,
            );

            Payment::create([
                'booking_id' => $booking->id,
                'order_id' => $orderId,
                'payment_type' => null,
                'payment_purpose' => $booking->payment_scheme === PaymentScheme::DP ? PaymentPurpose::DP : PaymentPurpose::LUNAS,
                'snap_token' => $snapToken,
                'snap_token_expiry' => Carbon::now()->addHour(),
                'amount' => $grossAmount,
                'status' => PaymentStatus::PENDING,
            ]);

            $booking->update(['status' => BookingStatus::PENDING]);

            $message = $this->buildPromotionMessage($booking);
            SendWhatsappNotificationJob::dispatch($user->phone, $message);
        });
    }

    private function buildPromotionMessage(Booking $booking): string
    {
        $userName = $booking->user?->name ?? 'Pelanggan';
        $code = $booking->booking_code;
        $date = Formatter::dateId($booking->booking_date, 'l, d F Y');
        $time = Formatter::timeRange($booking->start_time, $booking->end_time);

        return "Halo {$userName},\n\n"
            . "Ada informasi terbaru mengenai pesanan Anda. Jadwal sesi yang Anda inginkan kini tersedia:\n\n"
            . "*Detail Booking:*\n"
            . "- Kode Booking : {$code}\n"
            . "- Jadwal Sesi : {$date} | {$time}\n\n"
            . "Mohon segera selesaikan pembayaran di halaman Dashboard Anda dalam waktu maksimal 1 jam ke depan untuk mengonfirmasi jadwal ini.\n\n"
            . "Terima kasih.";
    }
}
