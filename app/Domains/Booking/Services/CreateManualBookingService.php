<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\DTOs\BookingData;
use App\Domains\Booking\DTOs\ManualBookingData;
use App\Domains\Booking\Enums\BookingSource;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Enums\PaymentScheme;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Repositories\BookingRepository;
use App\Domains\Booking\Traits\CalculatesBookingTotal;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CreateManualBookingService
{
    use CalculatesBookingTotal;

    public function __construct(
        private readonly BookingRepository $repository,
        private readonly BookingCodeGenerator $codeGenerator
    ) {}

    /**
     * Create data booking manual dan data payment
     * @param ManualBookingData $data
     */
    public function execute(ManualBookingData $data): Booking
    {
        $lock = Cache::lock("booking_checkout_{$data->booking_date}_{$data->start_time}", 10);

        try {
            return $lock->block(5, function () use ($data) {
                return DB::transaction(function () use ($data) {
                    $variant = PackageVariant::with('package.category')->findOrFail($data->package_variant_id);
                    $status = BookingStatus::from($data->status);

                    // Otomatis tentukan scheme dari status input
                    $paymentScheme = $status === BookingStatus::SUCCESS
                      ? PaymentScheme::LUNAS : PaymentScheme::DP;

                    // Passing objek $variant langsung, hindari N+1 query
                    $totalPrice = $this->calculateTotal($variant, $data->addons ?? []);

                    $endTime = Carbon::parse($data->start_time)
                        ->addMinutes($variant->duration)
                        ->format('H:i');

                    if ($this->repository->isSlotOccupied($data->booking_date, $data->start_time, $endTime)) {
                        throw new \RuntimeException('Slot waktu sudah terisi. Silakan pilih jam lain.');
                    }

                    $bookingCode = $this->codeGenerator->generate(
                        $variant->package->category->category_code,
                        $variant->id,
                        $data->booking_date
                    );

                    $booking = $this->repository->create(new BookingData(
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
                    ));

                    if (! empty($data->addons)) {
                        $addonIds = array_column($data->addons, 'addon_id');
                        $addonsModels = Addon::whereIn('id', $addonIds)->get()->keyBy('id');

                        $syncData = [];
                        foreach ($data->addons as $item) {
                            $addon = $addonsModels->get($item['addon_id']);
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
                            'payment_type' => $data->payment_type ?? 'manual',
                            'payment_purpose' => PaymentPurpose::DP,
                            'amount' => $totalPrice * 0.6,
                            'status' => PaymentStatus::SETTLEMENT,
                            'pay_date' => now(),
                        ]);
                    } else {
                        Payment::create([
                            'booking_id' => $booking->id,
                            'order_id' => $booking->booking_code,
                            'payment_type' => $data->payment_type ?? 'manual',
                            'payment_purpose' => PaymentPurpose::LUNAS,
                            'amount' => $totalPrice,
                            'status' => PaymentStatus::SETTLEMENT,
                            'pay_date' => now(),
                        ]);
                    }

                    return $booking;
                });
            });
        } catch (LockTimeoutException $e) {
            throw new \RuntimeException('Sistem sedang memproses pesanan di tanggal ini secara bersamaan, silakan coba lagi.');
        }
    }
}
