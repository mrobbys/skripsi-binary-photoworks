<?php

namespace App\Domains\Booking\Http\Controllers\Backdoor;

use App\Domains\Booking\DTOs\SessionScheduleIndexData;
use App\Domains\Booking\Enums\BookingStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\MasterData\Models\Schedule;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SessionScheduleController extends Controller
{
  /**
   * Halaman Index: Daftar Jadwal Sesi Foto.
   */
  public function index(Request $request): View|JsonResponse
  {
    if ($request->wantsJson()) {
      try {
        $query = Booking::with(['user', 'packageVariant.package', 'background'])
          ->whereIn('status', [
            BookingStatus::DP_PAID,
            BookingStatus::SUCCESS,
            BookingStatus::DONE,
          ])
          ->orderBy('booking_date', 'desc')
          ->orderBy('start_time', 'desc');

        if ($search = $request->input('search')) {
          $query->where(function ($q) use ($search) {
            $q->where('booking_code', 'ilike', "%{$search}%")
              ->orWhereHas(
                'user',
                fn($u) => $u
                  ->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
              )
              ->orWhereHas(
                'packageVariant',
                fn($v) => $v
                  ->where('name', 'ilike', "%{$search}%")
                  ->orWhereHas('package', fn($p) => $p->where('name', 'ilike', "%{$search}%"))
              );
          });
        }

        if ($date = $request->input('date')) {
          $query->whereDate('booking_date', $date);
        }

        $limit = max(1, min((int) $request->query('limit', 10), 100));
        $bookings = $query->paginate($limit);

        $today = Carbon::today();

        $totalToday = Booking::whereDate('booking_date', $today)
          ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS, BookingStatus::DONE])
          ->count();
        $doneToday = Booking::whereDate('booking_date', $today)
          ->where('status', BookingStatus::DONE)
          ->count();
        $upcomingTotal = Booking::where('booking_date', '>', $today)
          ->whereIn('status', [BookingStatus::DP_PAID, BookingStatus::SUCCESS])
          ->count();

        return response()->json([
          'success' => true,
          'data' => SessionScheduleIndexData::collect($bookings->items()),
          'current_page' => $bookings->currentPage(),
          'last_page' => $bookings->lastPage(),
          'total' => $bookings->total(),
          'total_today' => $totalToday,
          'done_today' => $doneToday,
          'upcoming_total' => $upcomingTotal,
        ]);
      } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
      }
    }

    return view('backdoor.session-schedule.index');
  }

  /**
   * Halaman Kalender Sesi.
   */
  public function calendar(): View
  {
    return view('backdoor.session-schedule.calendar');
  }

  /**
   * API events untuk fullcalendar
   * @param Request $request
   */
  public function events(Request $request): JsonResponse
  {
    $start = $request->input('start');
    $end = $request->input('end');

    /**
     * Booking events
     * Ambil data booking yang status = DP_PAID, SUCCESS, DONE
     */
    $bookings = Booking::with(['user', 'packageVariant.package'])
      ->whereIn('status', [
        BookingStatus::DP_PAID,
        BookingStatus::SUCCESS,
        BookingStatus::DONE,
      ])
      ->when($start, fn($q) => $q->where('booking_date', '>=', \Carbon\Carbon::parse($start)->toDateString()))
      ->when($end,   fn($q) => $q->where('booking_date', '<=', \Carbon\Carbon::parse($end)->toDateString()))
      ->orderBy('booking_date')
      ->orderBy('start_time')
      ->get();

    $events = $bookings->map(function (Booking $booking) {
      $packageName = $booking->packageVariant->package->name;
      $startTime = $booking->booking_date->format('Y-m-d') . 'T' . $booking->start_time->format('H:i:s');
      $endTime = $booking->booking_date->format('Y-m-d') . 'T' . $booking->end_time->format('H:i:s');

      return [
        'id' => 'booking-' . $booking->id,
        'title' => $packageName . ' - ' . $booking->user->name,
        'start' => $startTime,
        'end' => $endTime,
        'url' => route('backdoor.session-schedule.list.show', $booking->booking_code),
        'color' => $booking->status === BookingStatus::DONE
          ? '#a8a29e'
          : '#44403c',
        'extendedProps' => [
          'booking_code' => $booking->booking_code,
          'status'       => $booking->status->value,
        ],
      ];
    })->toArray();

    // Hari libur berdasarkan data dari Schedule
    $holidays = Schedule::where('is_active', false)->get();

    /**
     * Convert hari dari DB ISO -> fullcalendar
     * (1=Senin, 2=Selasa) -> (0=Senin, 1=Selasa)
     * Formula = $schedule->day->value % 7
     */
    foreach ($holidays as $schedule) {
      $fcDow = $schedule->day->value % 7;

      $events[] = [
        'id' => 'holiday-' . $schedule->day->value,
        'title' => 'Hari Libur',
        'daysOfWeek' => [$fcDow],
        'display' => 'background',
        'color' => '#fce7f3',
        'allDay' => true,
      ];
    }

    return response()->json($events);
  }
}
