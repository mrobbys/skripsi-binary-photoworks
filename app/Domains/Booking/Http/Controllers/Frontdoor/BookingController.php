<?php

namespace App\Domains\Booking\Http\Controllers\Frontdoor;

use App\Domains\Booking\DTOs\BookingViewData;
use App\Domains\Booking\DTOs\CheckoutData;
use App\Domains\Booking\Http\Requests\CheckoutRequest;
use App\Domains\Booking\Repositories\BookingRepository;
use App\Domains\Booking\Services\BookingService;
use App\Domains\MasterData\Models\Addon;
use App\Domains\MasterData\Models\Category;
use App\Domains\MasterData\Models\Package;
use App\Domains\MasterData\Models\PackageVariant;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Domains\MasterData\Repositories\BackgroundRepository;
use App\Support\Formatter;
use App\Domains\MasterData\Repositories\ScheduleRepository;

#[Middleware('auth', only: ['flow', 'checkout', 'success'])]
class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly BookingRepository $repository,
        private readonly BackgroundRepository $backgroundRepository,
        private readonly ScheduleRepository $scheduleRepository
    ) {}

    /**
     * Tampil semua data paket
     * @param Request $request
     */
    public function services(Request $request): View|JsonResponse
    {
        if ($request->wantsJson()) {
            // ambil data paket yang aktif, beserta kategori dan variant yang aktif
            $query = Package::where('is_active', true)
                ->with(['category', 'variants' => function ($q) {
                    $q->where('is_active', true)->orderBy('price');
                }]);

            // filter berdasarkan kategori
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
                $isWhatsappOnly = $package->variants->isNotEmpty() && $package->variants->every(fn($v) => $v->is_whatsapp_only);
                $imageUrl = $package->getFirstMediaUrl('package-image');

                return array_merge($package->toArray(), [
                    'min_price_formatted' => Formatter::rupiah($minPrice),
                    'category_name' => $package->category?->name ?? '',
                    'is_whatsapp_only' => $isWhatsappOnly,
                    'image_url' => $imageUrl,
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

    /**
     * Halaman flow booking paket
     * @param Package $package
     */
    public function flow(Package $package): View
    {
        // abort jika paket tidak aktif
        abort_if(! $package->is_active, 404);

        // ambil variant
        $variants = PackageVariant::with(['features'])
            ->where('package_id', $package->id)
            ->where('is_active', true)
            ->orderBy('price')
            ->get();

        $addons = Addon::where('is_active', true)->orderBy('name')->get();
        $activeDays = $this->scheduleRepository->getDays();
        // ambil background yang aktif, beserta URL gambar thumbnail
        $backgrounds = $this->backgroundRepository->queryActive()
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
        $request->validate([
            'date' => ['required', 'date'],
            'duration' => ['required', 'integer', 'min:1'],
        ]);

        $date = $request->date;
        $duration = (int) $request->duration;
        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;

        // ambil slot waktu
        $schedules = $this->scheduleRepository->queryActive()
            ->where('day', $dayOfWeek)
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        // cek apakah slot waktu sudah terisi
        $occupiedSlots = $this->repository->getOccupiedSlotsByDate($date);
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

        return response()->json(['slots' => $slots]);
    }

    /**
     * Proses checkout
     * @param CheckoutRequest $request
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            $data = CheckoutData::from($request);
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

    /**
     * Halaman booking berhasil
     * @param string $bookingCode
     */
    public function success(string $bookingCode): View
    {
        $booking = $this->repository->findByCode($bookingCode);
        abort_if(! $booking || $booking->user_id !== Auth::id(), 404);
        $bookingData = BookingViewData::from($booking);

        return view('frontdoor.booking.booking-success', ['booking' => $bookingData]);
    }
}
