<?php

namespace App\Domains\Booking\Services;

use App\Domains\Booking\DTOs\CheckoutData;
use App\Domains\Booking\Enums\BookingSource;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Enums\PaymentScheme;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Services\SlotAvailabilityService;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\MasterData\Models\Schedule;
use App\Support\Formatter;
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
	 * Ambil daftar paket terpaginasikan untuk katalog
	 * @param array $params
	 */
	public function getPaginatedPackages(array $params): array
	{
		$query = Package::select('id', 'category_id', 'name', 'slug')
			->where('is_active', true)
			->with([
				'category:id,name',
				'media',
				'variants' => function ($q) {
					$q->select('id', 'package_id', 'price', 'is_whatsapp_only')
						->where('is_active', true)->orderBy('price');
				},
			]);

		$categoryName = $params['category'] ?? 'Semua';
		if ($categoryName !== 'Semua') {
			$query->whereHas('category', function ($q) use ($categoryName) {
				$q->where('name', $categoryName);
			});
		}

		$limit = max(1, min((int) ($params['limit'] ?? 6), 100));
		$packages = $query->paginate($limit, ['*'], 'page', $params['page'] ?? null);

		$items = collect($packages->items())->map(function ($package) {
			$minPrice = $package->variants->min('price');
			$isWhatsappOnly = $package->variants->isNotEmpty() && $package->variants->every(fn($v) => $v->is_whatsapp_only);

			return [
				'id' => $package->id,
				'category_id' => $package->category_id,
				'name' => $package->name,
				'slug' => $package->slug,
				'category' => $package->category,
				'media' => $package->media,
				'variants' => $package->variants,
				'min_price_formatted' => Formatter::rupiah($minPrice),
				'category_name' => $package->category?->name ?? '',
				'is_whatsapp_only' => $isWhatsappOnly,
				'image_url' => $package->getFirstMediaUrl('package-image', 'webp') ?: $package->getFirstMediaUrl('package-image'),
			];
		});

		return [
			'data' => $items,
			'current_page' => $packages->currentPage(),
			'last_page' => $packages->lastPage(),
			'total' => $packages->total(),
		];
	}

	/**
	 * Ambil slot waktu yang tersedia
	 * @param string $date
	 * @param int $duration
	 * @return array
	 */
	public function getAvailableSlots(string $date, int $duration): array
	{
		$dayOfWeek = Carbon::parse($date)->dayOfWeekIso;

		$schedules = Schedule::where('is_active', true)
			->where('day', $dayOfWeek)
			->orderBy('start_time')
			->get(['start_time', 'end_time']);

		$occupiedSlots = $this->slotAvailabilityService->getOccupiedSlotsByDate($date);
		$slots = [];

		foreach ($schedules as $schedule) {
			$currentSlotStart = Carbon::parse($schedule->start_time);
			$scheduleEnd = Carbon::parse($schedule->end_time);

			while ($currentSlotStart->copy()->addMinutes($duration)->lessThanOrEqualTo($scheduleEnd)) {
				$currentSlotEnd = $currentSlotStart->copy()->addMinutes($duration);

				$slotStartTime = $currentSlotStart->format('H:i');
				$slotEndTime = $currentSlotEnd->format('H:i');

				$isPast = Carbon::parse("{$date} {$slotStartTime}")->isPast();

				$isOccupied = $isPast || $occupiedSlots->some(function ($booking) use ($slotStartTime, $slotEndTime) {
					$bookingStart = Carbon::parse($booking->start_time)->format('H:i');
					$bookingEnd = Carbon::parse($booking->end_time)->format('H:i');

					return $slotStartTime < $bookingEnd && $slotEndTime > $bookingStart;
				});

				if (! $isOccupied) {
					$slots[] = [
						'start_time' => $slotStartTime,
						'end_time' => $slotEndTime,
					];
				}

				$currentSlotStart->addMinutes($duration);
			}
		}

		return $slots;
	}

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

					// preload addons sekali, hindari query duplikat
					$preloadedAddons = null;
					if (!empty($data->addons)) {
						$addonIds = array_column($data->addons, 'addon_id');
						$preloadedAddons = Addon::select('id', 'name', 'price')
							->whereIn('id', $addonIds)->get()->keyBy('id');

						$missingAddons = array_diff($addonIds, $preloadedAddons->pluck('id')->all());
						if ($missingAddons) {
							throw new \InvalidArgumentException('Addon tidak ditemukan.');
						}
					}

					$totalPrice = $this->calculateTotal($variant, $data->addons ?? [], $preloadedAddons);

					$endTime = Carbon::parse($data->start_time)
						->addMinutes($variant->duration)
						->format('H:i');

					if ($this->slotAvailabilityService->isSlotOccupied($data->booking_date, $data->start_time, $endTime)) {
						throw new \RuntimeException('Slot waktu sudah terisi. Silakan pilih jam lain.');
					}

					$bookingCode = $this->codeGenerator->generate(
						$variant->package->category->category_code,
						$variant->id,
						$data->booking_date,
					);

					$booking = Booking::create([
						'user_id' => $user->id,
						'package_variant_id' => $data->package_variant_id,
						'background_id' => $data->background_id ?? null,
						'booking_code' => $bookingCode,
						'booking_date' => $data->booking_date,
						'start_time' => $data->start_time,
						'end_time' => $endTime,
						'total_price' => $totalPrice,
						'payment_scheme' => $data->payment_scheme,
						'notes' => $data->notes,
						'status' => BookingStatus::PENDING,
						'source' => BookingSource::FRONTDOOR,
					]);

					// sync addons pakai preloaded, bukan query ulang
					if ($preloadedAddons) {
						$syncData = [];
						foreach ($data->addons as $item) {
							$addon = $preloadedAddons->get($item['addon_id']);
							$quantity = $addon && !$addon->has_quantity ? 1 : ($item['quantity'] ?? 1);
							$syncData[$item['addon_id']] = [
								'price_at_purchase' => $addon ? $addon->price : 0,
								'quantity' => $quantity,
							];
						}
						$booking->addons()->sync($syncData);
					}

					$grossAmount = $data->payment_scheme === PaymentScheme::DP
						? (int) round($totalPrice * PaymentScheme::DP_RATE)
						: $totalPrice;

					$suffix = $data->payment_scheme === PaymentScheme::DP ? 'DP' : 'FULL';
					$randomString = Str::upper(Str::random(3));
					$orderId = "{$bookingCode}-{$suffix}-{$randomString}";

					$snapToken = $this->midtrans->getSnapToken(
						orderId: $orderId,
						grossAmount: $grossAmount,
						user: $user,
						bookingId: $booking->id,
						bookingCode: $bookingCode,
						packageName: $variant->package->name,
						variantName: $variant->name,
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
