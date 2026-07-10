<?php

namespace App\Domains\User\Http\Controllers\Frontdoor;

use App\Domains\Booking\Services\DashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {}

    /**
     * Tampilkan halaman dashboard Jadwal (Booking History)
     */
    public function index(): View
    {
        return view('frontdoor.dashboard.jadwal');
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
            $snapToken = $this->dashboardService->getOrCreateSnapToken(
                bookingCode: $request->booking_code,
                user: Auth::user(),
            );

            return response()->json([
                'success'    => true,
                'snap_token' => $snapToken,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Tampilkan halaman Profil
     */
    public function profil(): View
    {
        return view('frontdoor.dashboard.profil');
    }
}
