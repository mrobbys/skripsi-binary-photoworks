<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\DTOs\BookingData;
use App\Domains\Booking\DTOs\ManualBookingData;
use App\Domains\Booking\Enums\BookingSource;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Enums\PaymentScheme;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Services\SlotAvailabilityService;
use App\Domains\Booking\Traits\CalculatesBookingTotal;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Jobs\SendWhatsappNotificationJob;
use App\Support\Formatter;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CreateManualBookingService
{
    use CalculatesBookingTotal;

    public function __construct(
        private readonly SlotAvailabilityService $slotAvailabilityService,
        private readonly BookingCodeGenerator $codeGenerator
    ) {}

    /**
     * Create data booking manual dan data payment
     */
    public function execute(ManualBookingData $data): Booking
    {
        // Lock per-tanggal (bukan per tanggal+waktu) agar semua booking
        // di tanggal yang sama saling mengantri dan mencegah overlap antar-slot.
        $lock = Cache::lock("booking_checkout_{$data->booking_date}", 10);

        try {
            $bookingResult = $lock->block(5, function () use ($data) {
                return DB::transaction(function () use ($data) {
                    $variant = PackageVariant::with('package.category')->findOrFail($data->package_variant_id);

                    $status = BookingStatus::from($data->status);
                    if (! in_array($status, [BookingStatus::DP_PAID, BookingStatus::SUCCESS])) {
                        throw new \InvalidArgumentException('Status booking tidak valid untuk pembuatan manual.');
                    }

                    // Otomatis tentukan scheme dari status input
                    $paymentScheme = $status === BookingStatus::SUCCESS
                        ? PaymentScheme::LUNAS : PaymentScheme::DP;

                    // Fetch addons sekali, reuse untuk calculateTotal & sync
                    $addonModels = ! empty($data->addons)
                        ? Addon::whereIn('id', array_column($data->addons, 'addon_id'))->get()->keyBy('id')
                        : collect();

                    // Passing $addonModels agar trait tidak query ulang ke DB
                    $totalPrice = $this->calculateTotal($variant, $data->addons ?? [], $addonModels);

                    $endTime = Carbon::parse($data->start_time)
                        ->addMinutes($variant->duration)
                        ->format('H:i');

                    if ($this->slotAvailabilityService->isSlotOccupied($data->booking_date, $data->start_time, $endTime)) {
                        throw new \RuntimeException('Slot waktu sudah terisi. Silakan pilih jam lain.');
                    }

                    $bookingCode = $this->codeGenerator->generate(
                        $variant->package->category->category_code,
                        $variant->id,
                        $data->booking_date
                    );

                    $booking = Booking::create((new BookingData(
                        user_id: $data->user_id,
                        package_variant_id: $data->package_variant_id,
                        background_id: $data->background_id,
                        booking_code: $bookingCode,
                        booking_date: $data->booking_date,
                        start_time: $data->start_time,
                        end_time: $endTime,
                        total_price: $totalPrice,
                        payment_scheme: $paymentScheme,
                        status: $status,
                        source: BookingSource::MANUAL,
                    ))->toArray());

                    // Reuse $addonModels yang sudah di-fetch di atas (Fix 3)
                    if (! empty($data->addons)) {
                        $syncData = [];
                        foreach ($data->addons as $item) {
                            $addon = $addonModels->get($item['addon_id']);
                            $syncData[$item['addon_id']] = [
                                'price_at_purchase' => $addon ? $addon->price : 0,
                                'quantity' => $item['quantity'] ?? 1,
                            ];
                        }
                        $booking->addons()->sync($syncData);
                    }

                    if ($status === BookingStatus::DP_PAID) {
                        Payment::create([
                            'booking_id' => $booking->id,
                            'order_id' => $booking->booking_code,
                            'payment_type' => 'manual',
                            'payment_purpose' => PaymentPurpose::DP,
                            'amount' => $totalPrice * PaymentScheme::DP_RATE,
                            'status' => PaymentStatus::SETTLEMENT,
                            'pay_date' => now(),
                        ]);
                    } else {
                        Payment::create([
                            'booking_id' => $booking->id,
                            'order_id' => $booking->booking_code,
                            'payment_type' => 'manual',
                            'payment_purpose' => PaymentPurpose::LUNAS,
                            'amount' => $totalPrice,
                            'status' => PaymentStatus::SETTLEMENT,
                            'pay_date' => now(),
                        ]);
                    }

                    return $booking;
                });
            });

            // Dispatch notifikasi WA di luar block database transaction agar jika gagal tidak rollback pesanan
            if ($data->send_wa_notification) {
                // Relasi dipastikan terload untuk notifikasi
                $bookingResult = Booking::with(['user', 'packageVariant.package', 'addons'])->find($bookingResult->id);
                SendWhatsappNotificationJob::dispatch(
                    $bookingResult->user->phone,
                    $this->buildWaMessage($bookingResult)
                );
            }

            return $bookingResult;
        } catch (LockTimeoutException $e) {
            throw new \RuntimeException('Sistem sedang memproses pesanan di tanggal ini secara bersamaan, silakan coba lagi.');
        }
    }

    /**
     * Membangun template pesan WhatsApp untuk Booking Manual
     */
    private function buildWaMessage(Booking $booking): string
    {
        $code = $booking->booking_code;
        $user = $booking->user?->name;
        $package = $booking->packageVariant?->package?->name;
        $variant = $booking->packageVariant?->name;
        $bookingDate = Formatter::dateId($booking->booking_date, 'l, d F Y');
        $sessionTime = Formatter::timeRange($booking->start_time, $booking->end_time);
        $totalPrice = Formatter::rupiah($booking->total_price);
        $statusStr = $booking->status->label();

        $dashboardUrl = route('frontdoor.dashboard.index');

        $message = <<<TEXT
Halo {$user}, pesanan Anda telah berhasil kami catat!

Berikut adalah rincian booking Anda:
*Kode Booking*: {$code}
*Paket*: {$package} ({$variant})
*Tanggal*: {$bookingDate}
*Waktu*: {$sessionTime} WITA
*Total Biaya*: {$totalPrice}
*Status Pembayaran*: {$statusStr}

TEXT;

        // Fix 4: Hoist $receiptUrl keluar dari if/else (tidak duplikasi)
        $receiptUrl = route('payments.receipt', ['booking' => $booking->booking_code]);

        if ($booking->status === BookingStatus::DP_PAID) {
            $dpAmount = Formatter::rupiah($booking->total_price * PaymentScheme::DP_RATE);
            $sisaAmount = Formatter::rupiah($booking->total_price * (1 - PaymentScheme::DP_RATE));

            $message .= "\nAnda telah membayarkan DP sebesar {$dpAmount}. Sisa pelunasan sebesar {$sisaAmount} dapat dibayarkan di studio nanti.";
            $message .= "\n\nUnduh Bukti Pembayaran DP Anda:\n{$receiptUrl}";
            $message .= "\n\nPantau status jadwal Anda di Dasbor Klien:\n{$dashboardUrl}";
        } else {
            $message .= "\nPembayaran Anda sudah *LUNAS*. Terima kasih!";
            $message .= "\n\nUnduh Bukti Pembayaran Anda:\n{$receiptUrl}";
            $message .= "\n\nPantau status jadwal Anda di Dasbor Klien:\n{$dashboardUrl}";
        }

        $message .= "\n\nTerima kasih telah mempercayakan momen berharga Anda kepada kami! Sampai jumpa di studio!";

        return $message;
    }
}
