<?php

namespace App\Domains\User\Http\Controllers\Backdoor;

use App\Domains\Booking\DTOs\ClientBookingHistoryData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\User\DTOs\ClientDataIndexData;
use App\Domains\User\DTOs\ClientDataShowData;
use App\Domains\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\View\View;

#[Middleware('permission:clientData-view')]
class ClientDataController extends Controller
{
  /**
   * Halaman daftar klien (index)
   */
  public function index(): View
  {
    $totalClients = User::role('user')->count();

    $newClientsThisMonth = User::role('user')
      ->whereMonth('created_at', now()->month)
      ->whereYear('created_at', now()->year)
      ->count();

    return view('backdoor.client-data.index', [
      'totalClients' => $totalClients,
      'newClientsThisMonth' => $newClientsThisMonth
    ]);
  }

  /**
   * API JSON untuk table index
   * @param Request $request
   */
  public function data(Request $request): JsonResponse
  {
    $search = $request->input('search', '');
    $limit = max(1, min((int) $request->integer('limit', 10), 100));

    $query = User::role('user')
      ->select(['id', 'uuid', 'name', 'email', 'phone', 'created_at'])
      ->withCount('bookings as bookings_count')
      ->when($search, function ($q) use ($search) {
        $q->where(function ($inner) use ($search) {
          $inner->where('name', 'ilike', "%{$search}%")
            ->orWhere('email', 'ilike', "%{$search}%")
            ->orWhere('phone', 'ilike', "%{$search}%");
        });
      })
      ->latest('created_at');

    $paginated = $query->paginate($limit);

    return response()->json([
      'data' => ClientDataIndexData::collect($paginated->items()),
      'current_page' => $paginated->currentPage(),
      'last_page' => $paginated->lastPage(),
      'total' => $paginated->total(),
    ]);
  }

  /**
   * Halaman detail klien (show)
   * @param User $user
   */
  public function show(User $user): View
  {
    $user->loadCount([
      'bookings as total_all',
      'bookings as total_pending' => fn($q) => $q->where('status', BookingStatus::PENDING),
      'bookings as total_dp' => fn($q) => $q->where('status', BookingStatus::DP_PAID),
      'bookings as total_success' => fn($q) => $q->where('status', BookingStatus::SUCCESS),
      'bookings as total_done' => fn($q) => $q->where('status', BookingStatus::DONE),
      'bookings as total_cancel' => fn($q) => $q->where('status', BookingStatus::CANCELLED),
    ]);

    return view('backdoor.client-data.show', [
      'client' => ClientDataShowData::fromModel($user)
    ]);
  }

  /**
   * API JSON untuk table show
   * @param Request $request
   * @param User $user
   */
  public function bookings(Request $request, User $user): JsonResponse
  {
    $search = $request->input('search', '');
    $limit = max(1, min((int) $request->integer('limit', 10), 100));

    $query = $user->bookings()
      ->select(['id', 'user_id', 'package_variant_id', 'booking_code', 'booking_date', 'start_time', 'end_time', 'status', 'total_price'])
      ->with([
        'packageVariant:id,package_id,name',
        'packageVariant.package:id,name'
      ])
      ->when($search, function ($q) use ($search) {
        $q->where(function ($inner) use ($search) {
          $inner->where('booking_code', 'ilike', "%{$search}%")
            ->orWhereHas('packageVariant.package', function ($pkg) use ($search) {
              $pkg->where('name', 'ilike', "%{$search}%");
            });
        });
      })
      ->latest('booking_date');

    $paginated = $query->paginate($limit);

    return response()->json([
      'data' => ClientBookingHistoryData::collect($paginated->items()),
      'current_page' => $paginated->currentPage(),
      'last_page' => $paginated->lastPage(),
      'total' => $paginated->total(),
    ]);
  }
}
