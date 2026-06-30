<?php

namespace App\Domains\Booking\Http\Controllers\Frontdoor;

use App\Domains\Booking\Http\Requests\CheckoutRequest;
use App\Domains\Booking\Repositories\BookingRepository;
use App\Domains\Booking\Services\BookingService;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use App\Domains\MasterData\Models\Schedule;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Domains\MasterData\Models\Background;

#[Middleware('auth', only: ['flow', 'checkout', 'success'])]
class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly BookingRepository $repository,
    ) {}

    public function services(Request $request): View|JsonResponse
    {
        if ($request->wantsJson()) {
            $query = Package::where('is_active', true)
                ->with(['category', 'variants' => function ($q) {
                    $q->where('is_active', true)->orderBy('price');
                }]);

            $categoryName = $request->query('category', 'Semua');
            if ($categoryName !== 'Semua') {
                $query->whereHas('category', function ($q) use ($categoryName) {
                    $q->where('name', $categoryName);
                });
            }

            $limit = max(1, min((int) $request->query('limit', 6), 100));
            $packages = $query->paginate($limit);

            $items = collect($packages->items())->map(function ($package) {
                $minPrice = $package->variants->min('price');

                return array_merge($package->toArray(), [
                    'min_price_formatted' => number_format($minPrice ?? 0, 0, ',', '.'),
                    'category_name' => $package->category?->name ?? '',
                ]);
            });

            return response()->json([
                'data' => $items,
                'current_page' => $packages->currentPage(),
                'last_page' => $packages->lastPage(),
                'total' => $packages->total(),
            ]);
        }

        $categories = Category::where('is_active', true)->get();

        return view('frontdoor.services.index', compact('categories'));
    }

    public function flow(Package $package): View
    {
        abort_if(! $package->is_active, 404);

        $variants = PackageVariant::with(['features'])
            ->where('package_id', $package->id)
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        $addons = Addon::where('is_active', true)->orderBy('name')->get();
        $activeDays = Schedule::where('is_active', true)->pluck('day')->toArray();
        $backgrounds = Background::where('is_active', true)
            ->get()
            ->map(fn($bg) => [
                'id' => $bg->id,
                'name' => $bg->name,
                'image_url' => $bg->getFirstMediaUrl('background-image', 'thumb') ?: $bg->getFirstMediaUrl('background-image'),
            ]);

        return view('frontdoor.booking.flow', compact('package', 'variants', 'addons', 'activeDays', 'backgrounds'));
    }

    public function getAvailableSlots(Request $request): JsonResponse
    {
        $request->validate(['date' => ['required', 'date']]);

        $date = $request->date;
        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;

        $schedules = Schedule::where('is_active', true)
            ->where('day', $dayOfWeek)
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        $occupiedSlots = $this->repository->getOccupiedSlotsByDate($date);

        $slots = $schedules->map(function ($schedule) use ($occupiedSlots) {
            $isOccupied = $occupiedSlots->some(
                fn($b) => $schedule->start_time < $b->end_time && $schedule->end_time > $b->start_time
            );

            return [
                'start_time' => Carbon::parse($schedule->start_time)->format('H:i'),
                'end_time' => Carbon::parse($schedule->end_time)->format('H:i'),
                'is_occupied' => $isOccupied,
            ];
        });

        return response()->json(['slots' => $slots]);
    }

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            $data = $request->toDto();
            $result = $this->bookingService->processCheckout(Auth::user(), $data);

            return response()->json([
                'success' => true,
                'snap_token' => $result['snap_token'],
                'booking_code' => $result['booking_code'],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function success(string $bookingCode): View
    {
        $booking = $this->repository->findByCode($bookingCode);
        abort_if(! $booking || $booking->user_id !== Auth::id(), 404);

        return view('frontdoor.booking.booking-success', compact('booking'));
    }
}
