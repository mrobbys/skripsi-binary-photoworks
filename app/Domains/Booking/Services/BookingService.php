<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\DTOs\BookingData;
use App\Domains\Booking\DTOs\CheckoutData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Enums\PaymentScheme;
use App\Domains\Booking\Repositories\BookingRepository;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingService
{
    public function __construct(
        private readonly BookingRepository $repository,
        private readonly BookingCodeGenerator $codeGenerator,
        private readonly MidtransService $midtrans,
    ) {}

    /**
     * Hitung total harga booking
     * @param int $variantId
     * @param array $addons
     */
    public function calculateTotal(int $variantId, array $addons): int
    {
        // ambil harga variant
        $variant = PackageVariant::findOrFail($variantId);
        $base = $variant->price;

        // ambil semua addon, kemudian hitung total
        $addonTotal = collect($addons)->sum(function (array $item) {
            $addon = Addon::findOrFail($item['addon_id']);

            return $addon->price * ($item['quantity'] ?? 1);
        });

        // tambahkan harga variant dan addon
        return $base + $addonTotal;
    }

    /**
     * Proses checkout
     * @param User $user
     * @param CheckoutData $data
     */
    public function processCheckout(User $user, CheckoutData $data): array
    {
        return DB::transaction(function () use ($user, $data) {
            $variant = PackageVariant::with('package.category')->findOrFail($data->package_variant_id);
            $totalPrice = $this->calculateTotal($data->package_variant_id, $data->addons);

            $endTime = Carbon::parse($data->start_time)
                ->addMinutes($variant->duration)
                ->format('H:i');

            // cek apakah slot waktu sudah terisi
            if ($this->repository->isSlotOccupied($data->booking_date, $data->start_time, $endTime)) {
                throw new \RuntimeException('Slot waktu sudah terisi. Silakan pilih jam lain.');
            }

            // generate booking code
            $bookingCode = $this->codeGenerator->generate(
                $variant->package->category->category_code,
                $variant->id,
                $data->booking_date,
            );

            // buat data booking
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
                keterangan: $data->keterangan,
                status: BookingStatus::PENDING,
            ));

            // Attach addons ke pivot dengan price_at_purchase dan quantity
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

            // hitung total yang harus dibayar
            // jika dp, cukup bayar 60% nya saja
            $grossAmount = $data->payment_scheme === PaymentScheme::DP
                ? (int) round($totalPrice * 0.60)
                : $totalPrice;

            $suffix = $data->payment_scheme === PaymentScheme::DP ? 'DP' : 'FULL';
            $randomString = Str::upper(Str::random(4));
            // generate order id untuk midtrans
            $orderId = "{$bookingCode}-{$suffix}-{$randomString}";

            $snapToken = $this->midtrans->getSnapToken(
                orderId: $orderId,
                grossAmount: $grossAmount,
                user: $user,
                booking: $booking,
            );

            Payment::create([
                'booking_id' => $booking->id,
                'order_id' => $orderId,
                'payment_type' => null,
                'payment_purpose' => $data->payment_scheme === PaymentScheme::DP ? PaymentPurpose::DP : PaymentPurpose::LUNAS,
                'snap_token' => $snapToken,
                'amount' => $grossAmount,
                'status' => PaymentStatus::PENDING,
            ]);

            return ['booking_code' => $bookingCode, 'snap_token' => $snapToken];
        });
    }
}
