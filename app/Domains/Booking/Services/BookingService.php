<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\DTOs\BookingData;
use App\Domains\Booking\DTOs\CheckoutData;
use App\Domains\Booking\Enums\BookingSource;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Enums\PaymentScheme;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Services\SlotAvailabilityService;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\Payment\Enums\PaymentPurpose;
use App\Domains\Payment\Enums\PaymentStatus;
use App\Domains\Payment\Models\Payment;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Domains\Booking\Traits\CalculatesBookingTotal;
use Illuminate\Contracts\Cache\LockTimeoutException;

class BookingService
{
	use CalculatesBookingTotal;

	public function __construct(
		private readonly SlotAvailabilityService $slotAvailabilityService,
		private readonly BookingCodeGenerator $codeGenerator,
		private readonly MidtransService $midtrans,
	) {}

	/**
	 * Proses checkout
	 * @param User $user
	 * @param CheckoutData $data
	 */
	public function processCheckout(User $user, CheckoutData $data): array
	{
		// kunci proses berdasarkan tanggal booking untuk mencegah race condition (double booking pada slot waktu yang sama)
		$lock = Cache::lock("booking_checkout_{$data->booking_date}", 10);

		try {
			return $lock->block(5, function () use ($user, $data) {
				return DB::transaction(function () use ($user, $data) {
					$variant = PackageVariant::with('package.category')->findOrFail($data->package_variant_id);
					$totalPrice = $this->calculateTotal($variant, $data->addons ?? []);

					$endTime = Carbon::parse($data->start_time)
						->addMinutes($variant->duration)
						->format('H:i');

					// cek apakah slot waktu sudah terisi
					if ($this->slotAvailabilityService->isSlotOccupied($data->booking_date, $data->start_time, $endTime)) {
						throw new \RuntimeException('Slot waktu sudah terisi. Silakan pilih jam lain.');
					}

					// generate booking code
					$bookingCode = $this->codeGenerator->generate(
						$variant->package->category->category_code,
						$variant->id,
						$data->booking_date,
					);

					// buat data booking
					$booking = Booking::create((new BookingData(
						user_id: $user->id,
						package_variant_id: $data->package_variant_id,
						background_id: $data->background_id,
						booking_code: $bookingCode,
						booking_date: $data->booking_date,
						start_time: $data->start_time,
						end_time: $endTime,
						total_price: $totalPrice,
						payment_scheme: $data->payment_scheme,
						notes: $data->notes,
						status: BookingStatus::PENDING,
						source: BookingSource::FRONTDOOR,
					))->toArray());

					// Ambil semua model addon sekaligus
					if (!empty($data->addons)) {
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

					// hitung total yang harus dibayar
					// jika dp, cukup bayar 60% nya saja
					$grossAmount = $data->payment_scheme === PaymentScheme::DP
						? (int) round($totalPrice * PaymentScheme::DP_RATE)
						: $totalPrice;

					$suffix = $data->payment_scheme === PaymentScheme::DP ? 'DP' : 'FULL';
					$randomString = Str::upper(Str::random(3));
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
						'snap_token_expiry' => Carbon::now()->addHour(),
						'amount' => $grossAmount,
						'status' => PaymentStatus::PENDING,
					]);

					return ['booking_code' => $bookingCode, 'snap_token' => $snapToken];
				});
			});
		} catch (LockTimeoutException $e) {
			throw new \RuntimeException('Sistem sedang memproses pesanan di tanggal ini secara bersamaan, silakan coba lagi.');
		}
	}
}
