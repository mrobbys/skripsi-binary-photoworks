<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\DTOs\BookingData;
use App\Domains\Booking\DTOs\CheckoutData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Repositories\BookingRepository;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private readonly BookingRepository $repository,
        private readonly BookingCodeGenerator $codeGenerator,
        private readonly MidtransService $midtrans,
    ) {}

    public function calculateTotal(int $variantId, array $addons): int
    {
        $variant = PackageVariant::findOrFail($variantId);
        $base = $variant->price;

        $addonTotal = collect($addons)->sum(function (array $item) {
            $addon = Addon::findOrFail($item['addon_id']);

            return $addon->price * ($item['quantity'] ?? 1);
        });

        return $base + $addonTotal;
    }

    public function processCheckout(User $user, CheckoutData $data): array
    {
        return DB::transaction(function () use ($user, $data) {
            $variant = PackageVariant::with('package.category')->findOrFail($data->package_variant_id);
            $totalPrice = $this->calculateTotal($data->package_variant_id, $data->addons);

            $endTime = Carbon::parse($data->start_time)
                ->addMinutes($variant->duration)
                ->format('H:i');

            // Final server-side double-booking guard
            if ($this->repository->isSlotOccupied($data->booking_date, $data->start_time, $endTime)) {
                throw new \RuntimeException('Slot waktu sudah terisi. Silakan pilih jam lain.');
            }

            $bookingCode = $this->codeGenerator->generate(
                $variant->package->category->category_code,
                $variant->id,
                $data->booking_date,
            );

            $booking = $this->repository->create(new BookingData(
                user_id: $user->id,
                package_variant_id: $data->package_variant_id,
                background_id: $data->background_id,
                booking_code: $bookingCode,
                booking_date: $data->booking_date,
                start_time: $data->start_time,
                end_time: $endTime,
                total_price: $totalPrice,
                payment_scheme: $data->payment_scheme,
                status: BookingStatus::PENDING,
            ));

            // Attach addons ke pivot dengan price_at_purchase snapshot
            $syncData = collect($data->addons)->mapWithKeys(function (array $item) {
                $addon = Addon::findOrFail($item['addon_id']);

                return [
                    $item['addon_id'] => [
                        'price_at_purchase' => $addon->price,
                        'quantity' => $item['quantity'] ?? 1,
                    ],
                ];
            })->all();
            $booking->addons()->sync($syncData);

            $grossAmount = $data->payment_scheme === 'dp'
              ? (int) round($totalPrice * 0.60)
              : $totalPrice;

            $snapToken = $this->midtrans->getSnapToken(
                orderId: $bookingCode,
                grossAmount: $grossAmount,
                user: $user,
                booking: $booking,
            );

            Payment::create([
                'booking_id' => $booking->id,
                'order_id' => $bookingCode,
                'payment_type' => 'online',
                'payment_purpose' => $data->payment_scheme === 'dp' ? 'dp' : 'lunas',
                'snap_token' => $snapToken,
                'amount' => $grossAmount,
                'status' => PaymentStatus::PENDING,
            ]);

            return ['booking_code' => $bookingCode, 'snap_token' => $snapToken];
        });
    }
}
