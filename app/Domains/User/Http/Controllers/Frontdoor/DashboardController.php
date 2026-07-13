<?php

namespace App\Domains\User\Http\Controllers\Frontdoor;

use App\Domains\Booking\Services\DashboardService;
use App\Domains\MasterData\Repositories\ScheduleRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly ScheduleRepository $scheduleRepository
    ) {}

    /**
     * Tampilkan halaman dashboard Jadwal (Booking History)
     */
    public function index(): View
    {
        $activeDays = $this->scheduleRepository->getDays();
        return view('frontdoor.dashboard.jadwal', compact('activeDays'));
    }

    /**
     * Ambil semua data booking history milik user yang sedang login
     * @param Request $request
     */
    public function appointments(Request $request): JsonResponse
    {
        $tab = $request->query('tab', 'upcoming');
        $limit = max(1, min((int) $request->query('limit', 5), 50));

        $history = $this->dashboardService->getBookingHistory(Auth::id(), $tab, $limit);

        return response()->json([
            'data'         => $history->items(),
            'current_page' => $history->currentPage(),
            'last_page'    => $history->lastPage(),
            'total'        => $history->total(),
        ]);
    }

    /**
     * Endpoint "Bayar Sekarang" — Reuse atau buat Snap Token baru
     * @param Request $request
     */
    public function repay(Request $request): JsonResponse
    {
        $request->validate([
            'booking_code' => ['required', 'string'],
        ]);

        try {
            $snapToken = $this->dashboardService->getValidSnapToken(
                bookingCode: $request->booking_code,
                user: Auth::user(),
            );

            return response()->json([
                'success'    => true,
                'snap_token' => $snapToken,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan. Silahkan coba lagi.',
            ], 500);
        }
    }

    /**
     * Batalkan booking milik user yang sedang login.
     * @param Request $request
     */
    public function cancel(Request $request): JsonResponse
    {
        $request->validate([
            'booking_code' => ['required', 'string'],
        ]);

        try {
            $this->dashboardService->cancelBooking(
                bookingCode: $request->booking_code,
                userId: Auth::id(),
            );

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dibatalkan.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan. Silahkan coba lagi.',
            ], 500);
        }
    }

    /**
     * Ubah jadwal booking milik user yang sedang login.
     * @param Request $request
     */
    public function reschedule(Request $request): JsonResponse
    {
        $request->validate([
            'booking_code' => ['required', 'string'],
            'new_date'     => ['required', 'date', 'after:today'],
            'new_time'     => ['required', 'date_format:H:i'],
        ]);

        try {
            $this->dashboardService->rescheduleBooking(
                bookingCode: $request->booking_code,
                userId: Auth::id(),
                newDate: $request->new_date,
                newStartTime: $request->new_time,
            );

            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil diubah.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan. Silahkan coba lagi.',
            ], 500);
        }
    }
}
