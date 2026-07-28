<?php

namespace App\Domains\Booking\Http\Controllers\Frontdoor;

use App\Domains\Booking\DTOs\BookingViewData;
use App\Domains\Booking\DTOs\CheckoutData;
use App\Domains\Booking\Http\Requests\CheckoutRequest;
use App\Domains\Booking\Services\BookingService;
use App\Domains\Booking\Models\Booking;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\Background;
use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\MasterData\Models\Schedule;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
	public function __construct(
		private readonly BookingService $bookingService,
	) {}

	/**
	 * Tampil halaman semua data paket
	 */
	public function services(): View
	{
		$categories = Category::where('is_active', true)
			->withCount(['packages' => fn($q) => $q->where('is_active', true)])
			->get();

		return view('frontdoor.services.index', compact('categories'));
	}

	/**
	 * API daftar paket (JSON)
	 * @param Request $request
	 */
	public function servicesApi(Request $request): JsonResponse
	{
		$result = $this->bookingService->getPaginatedPackages($request->only(['category', 'limit', 'page']));
		return response()->json($result);
	}

	/**
	 * Halaman flow booking paket
	 * @param Package $package
	 */
    public function flow(Package $package): View
    {
        abort_if(! $package->is_active, 404);

        $package->load(['features', 'media']);

        $variants = PackageVariant::with([
            'features' => fn($q) => $q->select('id', 'description', 'featureable_id', 'featureable_type'),
        ])
            ->select('id', 'package_id', 'name', 'price', 'duration', 'is_whatsapp_only')
            ->where('package_id', $package->id)
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        $addons = Addon::select('id', 'name', 'price', 'description', 'has_quantity')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $activeDays = Schedule::where('is_active', true)->pluck('day')->toArray();

        $backgrounds = Background::with('media')
            ->select('id', 'name')
            ->where('is_active', true)
            ->get()
            ->map(fn($bg) => [
                'id' => $bg->id,
                'name' => $bg->name,
                'image_url' => $bg->getFirstMediaUrl('background-image', 'thumb') ?: $bg->getFirstMediaUrl('background-image'),
            ]);

        return view('frontdoor.booking.flow', compact('package', 'variants', 'addons', 'activeDays', 'backgrounds'));
    }

    /**
     * Ambil slot waktu yang tersedia dari Schedule
     * @param Request $request
     */
    public function getAvailableSlots(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'duration' => ['required', 'integer', 'min:1'],
        ]);

        $slots = $this->bookingService->getAvailableSlots($validated['date'], (int) $validated['duration']);

        return response()->json(['slots' => $slots]);
    }

	/**
	 * Proses checkout
	 * @param CheckoutRequest $request
	 */
	public function checkout(CheckoutRequest $request): JsonResponse
	{
		try {
			$data = CheckoutData::fromRequest($request);
			$result = $this->bookingService->processCheckout(Auth::user(), $data);

			return $this->successResponse(
				message: 'Checkout berhasil',
				extra: [
					'snap_token' => $result['snap_token'],
					'booking_code' => $result['booking_code'],
				],
			);
		} catch (\RuntimeException $e) {
			return $this->errorResponse(
				message: $e->getMessage(),
				status: 422,
			);
		} catch (\Exception $e) {
			return $this->errorResponse(
				message: 'Terjadi kesalahan sistem. Silakan coba lagi.',
				status: 500,
			);
		}
	}
	
	/**
	 * Halaman booking berhasil
	 * @param string $bookingCode
	 */
    public function success(string $bookingCode): View
    {
        $booking = Booking::where('booking_code', $bookingCode)
            ->with([
                'user:id,name,phone',
                'packageVariant:id,name,package_id',
                'packageVariant.package:id,name',
                'background:id,name',
                'payments:id,booking_id,amount,order_id',
            ])
            ->first();
        abort_if(! $booking || $booking->user_id !== Auth::id(), 404);
        $bookingData = BookingViewData::fromModel($booking);

        return view('frontdoor.booking.booking-success', ['booking' => $bookingData]);
    }
}
